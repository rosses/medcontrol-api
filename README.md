# Medcontrol api / project

API in Laravel Lumen w/ Docker

# SQL Server instance deploy:

cd /var/opt/

docker run -e "ACCEPT_EULA=Y" -e "MSSQL_SA_PASSWORD=yourpassword" -e "MSSQL_PID=Express" -p 1433:1433 -v //c/mount/sql:/var/opt/mssql/data -d mcr.microsoft.com/mssql/server:2019-latest

docker run -e "ACCEPT_EULA=Y" -e "MSSQL_SA_PASSWORD=yourpassword" -e "MSSQL_PID=Express" -p 1433:1433 -v sqlvolume:/var/opt/mssql -d {image_id}

# Install project

1. Clone the repo
2. 