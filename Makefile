# Makefile

# Define variables
COMPOSER_SCRIPT = composer.sh

# Define targets and their dependencies
up:	copyenv
	chmod 755 $(COMPOSER_SCRIPT)
	./$(COMPOSER_SCRIPT) install && ./$(COMPOSER_SCRIPT) update
	docker-compose up

down: cleanenv
	docker-compose down --rmi all

copyenv:
	find deployment/ -name "*.env" -exec cp {} app/ \;

cleanenv:
	find app/ -name "*.env" -exec rm {} \;