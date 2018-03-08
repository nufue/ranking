# Systém pro správu žebříčku LRU plavaná

## Požadavky
- [PHP 7.1](https://secure.php.net/)
- databáze kompabitilní s MySQL (MariaDB)
- [composer](https://getcomposer.org/)

## Instalace
- stáhnout z Gitu: `git clone https://github.com/nufue/ranking`
- nainstalovat závislosti pomocí `composer install --no-dev`
- vytvořit databázi (základní struktura je v `db/schema.sql`)
- přejmenovat `app/config/config.local.example.neon` na `config.local.neon` a vyplnit v něm údaje k databázi
- přejmenovat `app/config/authenticator.example.neon` na `authenticator.neon` a definovat v něm alespoň jednu dvojici `uživatel:heslo` účtu správce a zároveň roli `uživatel:role` (kde `role` je 'admin')
- v souboru `app/config/year.neon` změnit výchozí rok

## Struktura databáze
### Tabulka `competition_types`
Obsahuje jednotlivé typy závodů. Sloupce `year_from` a `year_to` umožňují postihnout platnost některých druhů závodů pouze v určitých ročnících (což se uplatní například při změnách juniorských kategorií nebo přidání nového typu závodu od určitého ročníku).

Pokud by nastala situace, že je třeba obnovit typu závodu, který je již ukončen - například _Územní přebor U12_, který se mohl konat v letech _2013_ až _2016_, provede se přidání nového záznamu:

`id = 'prebor_u12_2', description = 'Územní přebor U12', year_from = 2018, year_to = null`   
### Tabulka `competition_types_scoring`
Provazuje `competition_types` a `scoring_tables`.

### Tabulka `kategorie`
Obsahuje kategorie, jichž může být závodník členem.

### Tabulka `leagues`
Obsahuje seznam lig, které se každý rok konají. Pomocí sloupců `year_from` a `year_to` je možné existenci konkrétní ligy omezit na rozsah roků. 

### Tabulka `scoring_tables`
Provazuje `competition_types_scoring` a `scoring_tables_rows`.

### Tabulka `scoring_tables_rows`
Obsahuje bodové hodnoty jednotlivých umístění, rozlišených dle typu bodovací tabulky (odkaz do `scoring_tables`). Vstupem pro tuto tabulku je příloha č. 2 Soutěžního řádu - _Bodové hodnocení závodů  LRU – plavaná_. 

### Tabulka `team_name_override`
Slouží k přepsání automaticky vypočteného názvu týmu v žebříčku. U závodníků, kteří jsou napsáni na soupisce ligového týmu, se použije název ligového týmu. Pokud je závodník zapsán na soupisky více týmů v jednom roce (obvykle 1. a v 2. lize), použije se název týmu, ve kterém je zapsán na procentuelně vyšším místě. Pokud by takto určený název týmu nebyl žádoucí, je možné záznamem do této tabulky pro konkrétního závodníka a rok specifikovat konkrétní zázev týmu, který se má v žebříčku zobrazovat.  

### Tabulka `tymy`
Obsahuje týmy v jednotlivých ligách a rocích.
 
### Tabulka `tymy_zavodnici`
Provazuje ligové týmy (`tymy`) a jejich členy (`zavodnici`). 
### Tabulka `zavodnici`
Obsahuje registrované i neregistrované členy, kteří byli přítomni v importovaných výsledcích. 
### Tabulka `zavodnici_kategorie`
Obsahuje kategorii pro konkrétního závodníka v konkrétním roce.
### Tabulka `zavodnici_zavody`
Obsahuje výsledky závodníků v jednotlivých závodech. Protože většina závodů je dvoukolových, obsahuje sloupce pro dvě kola (`cips1`, `umisteni1`, `cips2`, `umisteni2`). V případě, že se závodník příslušného kola neúčastnil, uvede se do příslušné dvojice sloupců hodnota `NULL`. Pokud má závod více než dvě kola, je třeba založit v tabulce `zavody` více závodů (jeden závod na 1. a 2. kolo, druhý závod na 3. kolo). 
### Tabulka `zavody`
Obsahuje všechny v systému zadané závody. Každý závod má nějakou kategorii (`competition_types`). Některé závody mohou být omezeny co do kategorií závodníků, kteří se jich mohou zúčastnit - v takovém případě se nezapočtou do celkového žebříčku, nýbrž pouze do žebříčku příslušné kategorie (např. _U15_).

## Import dat
### Povinná pole
Každé importované výsledky musí obsahovat minimálně tyto sloupce:
- číslo registrace
- jméno závodníka
- družstvo
- kategorii
- počet bodů CIPS alespoň jednoho závodu
- umístění v alespoň jednom závodu


Pro každý záznam je třeba ověřit, zda-li:
- se jedná o registrovaného nebo neregistrovaného závodníka

### Neregistrovaný závodník
Ověří se, zda-li existuje v tabulce `zavodnici` dřívější záznam se stejným jménem.

Pokud existuje, zkontroluje se existence a rovnost kategorie v databázi a ve vstupních datech. Pokud obojí nastane, přiřadí se záznam k tomuto historickému záznamu. Pokud kategorie a databázi neexistuje, ale ve vstupních datech je, přidá se ke stávajícímu záznamu údaj o kategorii ze vstupních dat.
V ostatních případech se zahlásí _soft-error_.

Pokud záznam se jménem v databázi neexistuje, ověří se, že ve vstupních datech je zadána platná kategorie. Pokud ano, založí se nové záznamy v tabulkách `zavodnici` a `zavodnici_kategorie`. V ostatních případech se zahlásí _soft-error_.

### Registrovaný závodník
Ověří se, zda-li zadané číslo registrace existuje v tabulce `zavodnici`. Pokud ano, ověří se existence záznamu kategorie v databázi.

Pokud se podle čísla registrace závodníka v tabulce `zavodnici` vyhledat nepodaří, prohledá se též tabulka `registrace`. V takovém případě však musí být ve vstupních datech zadána hodnota `kategorie`. 

## Změny
### Přidání nové ligy
Doplní se nový záznam do tabulky `leagues`. Je vhodné vyplnit sloupec `year_from` rokem, od kterého liga existuje, aby se nezobrazovala v dřívějších letech.

### Změna označení stávající ligy
Přejmenování stávající ligy se řeší ukončením platnosti (vyplněním pole `year_to`) stávající ligy a vytvořením nového záznamu s novým označením.

### Přidání nové věkové kategorie
Přidá se nový záznam do tabulky `kategorie`. Pokud se vznikem nové kategorie vznikne také nový druh závodu, postupuje se podle návodu níže.

### Přidání nového bodovacího schématu

### Přidání nového typu závodu
Přidá se nový záznam do tabulky `competition_types`. Dále se novému typu závodu přiřadí bodovací schéma.

### Přiřazení bodového schématu typu závodu
Přidá se nový záznam do tabulky `competition_types_scoring`. 

### Změna týmu zobrazovaného v žebříčku
Přidá se nový záznam do tabulky `team_name_override`.

## Oprávnění
Zobrazení žebříčku, výsledků jednotlivého závodu nebo jednotlivého závodníka je možné bez přihlášení.

Přidávání a editace závodů, přidávání výsledků, přidávání ligových týmů a úprava jejich soupisek, změny jména a kategorií u závodníků je možné pouze pro přihlášené uživatele s rolí `admin`. Nastavení probíhá v souboru `app/config/authenticator.neon`. 