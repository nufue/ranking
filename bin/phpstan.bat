@setlocal DISABLEDELAYEDEXPANSION
@SET PARENT_DIR=%~dp0..
@SET BIN_TARGET=%PARENT_DIR%\vendor\bin\phpstan.bat
%BIN_TARGET% analyse --level 7 -c %PARENT_DIR%\config\phpstan.neon %PARENT_DIR%\app %PARENT_DIR%\tests