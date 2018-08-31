@echo off
echo 将删除所有临时生成的文件
pause
DEL /P /F %~dp0\wx_ddq\data\raw\Africa_data.xlsx
DEL /P /F %~dp0\wx_ddq\data\runtime\querylog.txt
DEL /P /F %~dp0\wx_ddq\logs\ci\log-2018-??-??.log
pause