# Systém pro správu žebříčku LRU plavaná

## Požadavky
- [PHP 7.1](https://secure.php.net/)
- databáze kompabitilní s MySQL (MariaDB)
- [composer](https://getcomposer.org/)
- webový server Apache se zapnutým `mod_rewrite` nebo nginx (s konfigurací pro vytváření pěkných URL)
- splněné požadavky [Nette Requirements Checkeru](https://github.com/nette/sandbox/blob/master/www/checker/index.php)

## Instalace
- stáhnout zdrojový kód z Gitu: `git clone https://github.com/nufue/ranking`
- v adresáři se staženým zdrojovým kódem nainstalovat závislosti pomocí `composer install --no-dev` (tento krok vytvoří složku `vendor` a její obsah)
- vytvořit databázi (základní struktura je v `db/schema.sql`)
- přejmenovat `app/config/config.local.example.neon` na `config.local.neon` a vyplnit v něm přístupové údaje k databázi
- přejmenovat `app/config/authenticator.example.neon` na `authenticator.neon` a definovat v něm alespoň jednu dvojici `uživatel:heslo` účtu správce a zároveň roli `uživatel:role` (kde `role` je 'admin')
- v souboru `app/config/year.neon` změnit výchozí rok

## Zabezpečení instalace
Pokud provozujete aplikace na serveru Apache a nemáte nasměrován `DocumentRoot` do složky `www`, je nutné do složek `app`, `bin`, `config`, `db`, `logs`, `temp` a `vendor` umístit soubor `.htaccess` s obsahem `Deny from all`, jinak bude vaše konfigurace (např. údaje pro připojení k databázi) přístupná lidem, kteří odhadnout správnou adresu.

## Struktura databáze
### Tabulka `competition_categories`
Obsahuje kategorie určující započítávání závodníků do žebříčku. Každý závod má přiřazenu právě jednu kategorii. Sloupec `output_description` obsahuje text, který se ve výpisu připojí k názvu závodu. Sloupec `select_description` obsahuje text, který se zobrazí v drop-down prvku v přidávání nebo editaci závodu. Podle pole `order` jsou v drop-down prvku záznamy seřazeny. Platnost jednotlivých záznamů je možno ovlivnit nastavením sloupců `year_from` a `year_to`. 

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

### Tabulka `team_members_count`
Obsahuje počet členů ligových družstev s uvedením platnosti od (`year_from`) a platnosti do (`year_to`). Oba sloupce mohou nabývat hodnoty `NULL`, což znamená, že platnost od/do není omezena. 

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
Obsahuje všechny v systému zadané závody. Každý závod je nějakého druhu (`competition_types`), přičemž druh určuje, jaké bodovací schéma se použije pro započtení výsledku závodu do žebříčku.
Některé závody mohou být omezeny co do kategorií závodníků (`competition_categories`), kteří se jich mohou zúčastnit - v takovém případě se nezapočtou do celkového žebříčku, nýbrž pouze do žebříčku příslušné kategorie (např. _U15_).

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
- jméno z výsledků odpovídá uloženému jménu z databáze
- ve výsledcích je uvedena kategorie nebo ji lze dohledat z dřívějšího závodu ve stejném roce

### Neregistrovaný závodník
Ověří se, zda-li existuje v tabulce `zavodnici` dřívější záznam se stejným jménem.

Pokud existuje, zkontroluje se existence a rovnost kategorie v databázi a ve vstupních datech. Pokud obojí nastane, přiřadí se záznam k tomuto historickému záznamu. Pokud kategorie a databázi neexistuje, ale ve vstupních datech je, přidá se ke stávajícímu záznamu údaj o kategorii ze vstupních dat.
V ostatních případech se zahlásí _soft-error_.

Pokud záznam se jménem v databázi neexistuje, ověří se, že ve vstupních datech je zadána platná kategorie. Pokud ano, založí se nové záznamy v tabulkách `zavodnici` a `zavodnici_kategorie`. V ostatních případech se zahlásí _soft-error_.

### Registrovaný závodník
Ověří se, zda-li zadané číslo registrace existuje v tabulce `zavodnici`.

Pokud ano, ověří se shoda jména - pokud se jméno neshoduje, je vypsáno upozornění. Obvykle je neshoda způsobena jednou z následujících příčin:
- překlep v zadaném čísle registrace (v upozornění se zobrazí úplně jiné jméno než je ve výsledcích) - v takovém případě se do zobrazeného textového pole vyplní správné číslo registrace
- překlep v zadaném jméně - můžeme ignorovat, v žebříčku se použije dříve zadané jméno (pokud je špatně, je možné jej změnit - viz níže)

Dále se ověří existence záznamu kategorie v databázi. Pokud záznam kategorie existuje, avšak nesouhlasí se vstupními údaji, je vypsáno upozornění.

Pokud zadané číslo registrace v tabulce `zavodnici` neexistuje a zároveň ve vstupních datech není zadána kategorie, zobrazí se formulář pro nastavení kategorie u konkrétního závodníka - bez jejího vyplnění není možné výsledky uložit.

## Provádění změn
### Úprava jména závodníka, jeho kategorie nebo čísla registrace
Přihlášený správce v horní liště klikne na odkaz `Závodníci`, pomocí formuláře vyhledá závodníka buď podle čísla registrace nebo části jména.

U některého z nalezených záznamů klikne na _Upravit_, zobrazí se stránka s možností úpravy jména, zadáním nového čísla registrace (používat s rozmyslem) a úpravou kategorie v jednotlivém roce.

### Přidání nové ligy
Doplní se nový záznam do tabulky `leagues`. Je vhodné vyplnit sloupec `year_from` rokem, od kterého liga existuje, aby se nezobrazovala v dřívějších letech.

### Změna označení stávající ligy
Přejmenování stávající ligy se řeší ukončením platnosti (vyplněním pole `year_to`) stávající ligy a vytvořením nového záznamu s novým označením.

### Přidání nové věkové kategorie
Přidá se nový záznam do tabulky `kategorie`. Pokud se vznikem nové kategorie vznikne také nový druh závodu, postupuje se podle návodu níže.

### Přidání nového bodovacího schématu
Do tabulky `scoring_tables` se přidá nový záznam s textovým popisem bodovacího schématu. Následně se do tabulky `scoring_tables_rows` přidají záznamy určující body (`points`) pro jednotlivá umístění (`rank`) v rámci bodovacího schématu (`id`).

### Přidání nového typu závodu
Přidá se nový záznam do tabulky `competition_types`. Dále se novému typu závodu přiřadí bodovací schéma.

### Přiřazení bodového schématu typu závodu
Přidá se nový záznam do tabulky `competition_types_scoring`. 

### Změna týmu zobrazovaného v žebříčku
Přidá se nový záznam do tabulky `team_name_override`.

## Oprávnění
Zobrazení žebříčku, výsledků jednotlivého závodu nebo jednotlivého závodníka je možné bez přihlášení.

Přidávání a editace závodů, přidávání výsledků, přidávání ligových týmů a úprava jejich soupisek, změny jména a kategorií u závodníků je možné pouze pro přihlášené uživatele s rolí `admin`. Nastavení probíhá v souboru `app/config/authenticator.neon`. 