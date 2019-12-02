# Overview
WebDKP is a website for managing DKP for World of Warcraft.

This is the official repo for the webdkp.com website.

## What is DKP?
DKP, short for “Dragon Kills Points”, is a method of rewarding items
to players in game based on their contribution. In general, users
receive ‘DKP Points’ whenever they participate in raid or help the
guild. They can then ‘spend’ these points when items drop during 
raids to purchase them. In this way, players who consistently help
the guild can fairly earn items.

## What does WebDKP do?
WebDKP helps manage your Guilds DKP and makes your life easier. It 
has two parts: an in game Addon for awarding and viewing DKP, and 
the site that allows you to share your DKP table online. The site
 and Addon are easy to use and can save hours of work.

# Development Setup
A pre-configured stack is provided as Docker images. It should
start up a local version of the site using Apache, PHP, MySQL,
and phpmyadmin. It will also prime your local database with
some initial site data.

## Steps
1. Install Docker ([Win/Mac](https://www.docker.com/products/docker-desktop) or [Linux](https://docs.docker.com/install/linux/docker-ce/ubuntu/))

2. Start the server
```shell
docker-compose up
```
3. Add a server
  * Open phpmyadmin at `http://localhost:8080/`
  * View the table `webdkp_main > dkp_servers`
  * Add your sever as a new table row

4. Visit `http://localhost` to see the site and register an account

# Deployments
See [deploy.md](./deploy.md)