@setlocal DISABLEDELAYEDEXPANSION
@SET PARENT_DIR=%~dp0..
@SET BIN_TARGET=%PARENT_DIR%\vendor\bin\parallel-lint
%BIN_TARGET% %PARENT_DIR%\app %PARENT_DIR%\tests