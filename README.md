# News Aggregator

A news aggregator api that crawls news platform

## Getting started

- Clone the project
- Setup docker on your machine
- Open terminal at the root of this project
- Copy the .env.example file to .env file `copy .env.example .env`.
- Run the command `php artisan key:generate`
- Add the necessary configurations to .env file especially the news api keys. You can leave the database vars as is for testing
- run `docker-compose up --build` to build and run the docker container
- If everything goes smoothly, then you should have the following:
- An API running at localhost:80
- phpmyadmin running at localhost:8002 which you can use to inspect the data
- You can test using [GET] http://127.0.0.1:80/api/v1/news

Under the hood, we have 
- setup the laravel app
- mysql database
- nginx server
- Ran the migrations and seeders
- Ran the scrap:news artisan command to get the initial data
- Setup a scheduler to scrap news at intervals.

> To configure what api providers are enabled, please see the config/services.php file.


## Known issues

- Upserting categories and articles guarantees auto-increment but doesn't guarantee that the IDs won't have gaps


## License

This is an open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
