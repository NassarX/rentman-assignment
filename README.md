# Assessment repo

To get the full instructions the first step is to get this docker setup running. If you want to go to the instructions
directly check the file under `app/instructions`


## Getting Started

This application is shipped with the Docker Compose environment and requires Docker to be installed locally and running.
If you're not familiar with Docker or don't have it locally, please reach out to 
[the official website](https://www.docker.com)
 to learn more and follow the Docker installation instructions to install it on your platform:   

[Docker for Mac](https://docs.docker.com/desktop/install/mac-install/)  
[Docker for Linux](https://docs.docker.com/desktop/get-started/)  
[Docker for Windows](https://docs.docker.com/desktop/install/windows-install/)

The test assignment application is containerized within three containers that have PHP, Mysql, and Phpmyadmin respectively. 

Included tools:
- php 8.0
- mysql 8.0.17
- composer 1.10
- phpunit 9.6

### Up and Running

Once you have Docker up and running please perform the following steps:

**1. Working directory**

Change the current working directory to `Rentman-Assessment`.  

**You can save time and run `make up` to see the magic happens or if you prefer the long road perform next steps.**

**2. Setup .env files**

Copy `deployment/env/enviroment.envt` to `app/.env`.

**3. Setup and run composer**

Give execution permissions to composer.sh (windows and linux)
```bash
chmod 755 composer.sh
```

Install the php autoloader
```bash
./composer.sh install && ./composer.sh update
```

**4. Run application**

Please execute the following command to run application containers:
    	
```bash
docker-compose up --detach
```

The container will be listening on port `7000` on your `localhost`, you can access the application server using the 
following URL: [http://localhost:7000](http://localhost:7000).

### Remove application

As soon as you are done you can stop the application:

    docker-compose down

or again you save time and stop container and remove the images by running `make down`

## Usage

After running these commmands, these urls are available:

- http://localhost:7000/ Portal page with the instructions
- http://localhost:7001/ phpMyAdmin

## Database
Please find the database related files under `deployment/mysql` folder.

Database Changes:
- Indexing:
  - `stock` column of `equipment` table to improve performance of `SELECT` queries.
  - `equipment`, `quantity`, (`start`, `end`) columns of `planning` table to improve performance of `SELECT` queries.

- Stored Procedures:
    - `get_equipment_stock` to get the stock of equipment by equipment id.
    - `get_planned_equipments_per_date` to get the planned equipment for a given date.
    - `get_equipment_timeline` to get the timeline of equipment by equipment on given period.

## Testing

To run the tests, please execute the following command:

```bash 
docker exec [containerID] /bin/bash

cd .. && ./vendor/bin/phpunit tests
```

## Solution Approach

- I've built a stored procedure `get_equipment_timeline` to calculate the planned quantity and available quantity for each day in the planning period [start date: end date] to draw such a timeline for equipment (Inspired by instruction example). The stored procedure is called by the two methods in our EquimentAvailabilityHelper class.

```sql
call get_equipment_timeline('2019-04-02', '2019-04-10', 1);
```

| id | date | stock | planned\_qty | available\_qty |
| :--- | :--- | :--- | :--- | :--- |
| 1 | 2019-04-02 | 15 | 3 | 12 |
| 1 | 2019-04-03 | 15 | 3 | 12 |
| 1 | 2019-04-04 | 15 | 4 | 11 |
| 1 | 2019-04-05 | 15 | 4 | 11 |
| 1 | 2019-04-06 | 15 | 4 | 11 |
| 1 | 2019-04-07 | 15 | 3 | 12 |
| 1 | 2019-04-08 | 15 | 3 | 12 |
| 1 | 2019-04-09 | 15 | 4 | 11 |
| 1 | 2019-04-10 | 15 | 4 | 11 |

- At that point it is easy to check if the equipment is available or not by checking the `available_qty` column. If the `available_qty` is greater than or equal to the `quantity` we want to plan at any time within the requested period, then the equipment is available.
- To check if the equipment is short, we just need to check if the `available_qty` is less than 0. If it is less than 0, then the equipment is short.

### Debugging
I've added a `print_r` to print the result in depth details to the browser. It is commented out by default. 
**To enable it, just uncomment it in the `isAvailable` and `getShortages` methods.**

```php
\Assessment\Availability\Todo\EquimentAvailabilityHelperAssessment::isAvailable(1, 12, '2019-04-02', '2019-04-05');
Array
(
    [0] => On 2019-04-02, planned: 3, available qty: 12, sufficient: true
    [1] => On 2019-04-03, planned: 3, available qty: 12, sufficient: true
    [2] => On 2019-04-04, planned: 4, available qty: 11, sufficient: false
)
##--------------------
false //expected return value
```

```php
\Assessment\Availability\Todo\EquimentAvailabilityHelperAssessment::getShortages('2019-04-02', '2019-04-05');
Array
(
    [14] => Array
        (
            [0] => On 2019-04-03, shortage: -2
            [1] => On 2019-04-04, shortage: -2
            [2] => On 2019-04-05, shortage: -2
        )

    [21] => Array
        (
            [0] => On 2019-04-03, shortage: -1
            [1] => On 2019-04-04, shortage: -1
            [2] => On 2019-04-05, shortage: -5
        )

    [39] => Array
        (
            [0] => On 2019-04-04, shortage: -1
            [1] => On 2019-04-05, shortage: -4
        )

    [42] => Array
        (
            [0] => On 2019-04-02, shortage: -4
            [1] => On 2019-04-03, shortage: -2
            [2] => On 2019-04-04, shortage: -2
        )

    [3] => Array
        (
            [0] => On 2019-04-02, shortage: -1
            [1] => On 2019-04-03, shortage: -1
        )

    [15] => Array
        (
            [0] => On 2019-04-05, shortage: -2
        )

    [12] => Array
        (
            [0] => On 2019-04-04, shortage: -3
            [1] => On 2019-04-05, shortage: -3
        )

    [31] => Array
        (
            [0] => On 2019-04-02, shortage: -5
            [1] => On 2019-04-03, shortage: -7
            [2] => On 2019-04-04, shortage: -7
            [3] => On 2019-04-05, shortage: -7
        )

    [4] => Array
        (
            [0] => On 2019-04-03, shortage: -2
            [1] => On 2019-04-04, shortage: -2
            [2] => On 2019-04-05, shortage: -2
        )

    [8] => Array
        (
            [0] => On 2019-04-02, shortage: -1
            [1] => On 2019-04-03, shortage: -1
        )

    [27] => Array
        (
            [0] => On 2019-04-02, shortage: -12
            [1] => On 2019-04-03, shortage: -12
            [2] => On 2019-04-04, shortage: -11
            [3] => On 2019-04-05, shortage: -8
        )

    [26] => Array
        (
            [0] => On 2019-04-02, shortage: -7
            [1] => On 2019-04-03, shortage: -1
        )

    [25] => Array
        (
            [0] => On 2019-04-03, shortage: -4
            [1] => On 2019-04-04, shortage: -4
            [2] => On 2019-04-05, shortage: -4
        )

)
##----------------
{
    "14": "-2",
    "21": "-5",
    "39": "-4",
    "42": "-2",
    "3": "-1",
    "15": "-2",
    "12": "-3",
    "31": "-7",
    "4": "-2",
    "8": "-1",
    "27": "-8",
    "26": "-1",
    "25": "-4"
} //expected return value
```

## Remarks

- If anything is unclear, just ask!