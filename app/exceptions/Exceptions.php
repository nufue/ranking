<?php

namespace App\Exceptions;

final class RegistrationForCompetitorNotFound extends \LogicException
{
}

final class CompetitorNotFound extends \LogicException {

}

final class LeagueNotFound extends \LogicException { }

final class CategoriesForThisYearAreNotSet extends \LogicException {}