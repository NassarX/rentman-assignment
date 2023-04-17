# Assessment repo

To get the full instructions the first step is to get this docker setup running. If you want to go to the instructions
directly check the file under `app/instructions`


## Get it running

These instructions assume docker is already running.

```
# Give execution permissions to composer.sh (windows and linux)
chmod 755 composer.sh

# install the php autoloader
./composer.sh install  

# run the environment
docker-compose up

```

After running these commmands, these urls are available:

- http://localhost:7000/ Portal page with the instructions
- http://localhost:7001/ phpMyAdmin

## Remarks

- If anything is unclear, just ask!
