@setlocal DISABLEDELAYEDEXPANSION
@SET PARENT_DIR=%~dp0..
@SET BIN_TARGET=%PARENT_DIR%\vendor\bin\tester.bat
%BIN_TARGET% %PARENT_DIR%\tests -c %PARENT_DIR%\tests\php.local.ini --coverage-src=app tests --coverage %PARENT_DIR%\coverage.html