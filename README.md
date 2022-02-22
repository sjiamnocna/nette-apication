# Nette APICation - Basic universal minified Nette API
Welcome to a little experiment over Nette Framework.

Its aim is to reduce the serverload while using Nette as API. <br/>
Nette is great and complex thing. So is APItte and other API tools.

This project don't solve every possible API.<br/>
It's very simple, minimalistic and lightweight (by design).

- replaces Nette\Application
- gets rid of unnecessary preloaded Nette stuff to improve performance
    - presenters (turned into Endpoints)
    - router (simplified and merged with Request)
    - some other unnecessary autoloaded things in the container
    - DI Services are created on-demand for each Endpoint
- Automatically processes data from [JS/Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API), that came via `STDIN`
- Use Tracy to display error over XHR/Fetch requests
- Introduces minimalistic AJAX API security
- Using [git@github.com:sjiamnocna/renette.git](https://github.com/sjiamnocna/renette) as frontend you can simplify React + Nette integration
    - if you clone this repo into `server/` directory within the project, you can use `npm start` or `yarn start` to run React (Node) and PHP dev server (**DEV only, don\'t use on public**)
    - more coming (hopefully autodeploy and maybe generating docker container)
- If you dont like anything, feel free to override

## Dont get me wrong, I love Nette!
Nette, Latte etc. are great. It's IMHO just not efficient enough for modern "API" world

## Usage

- Begin by `composer install`
- Create `temp` and `log` directories for Nette to work
- Add service to configuration `app/config/common.neon`
```
services:
	Application: APIcation\Application(%parameters%)
```
- Create `app/config/local.neon` like this (**don't ever add to GIT!!!**);
```
    parameters:
        service: # used for service authentication
            service: privatekey
        #...other param
```

Start both server and local React app (CRA) using `yarn start` from the root directory (the one above `server/`)

OR use Makefile: `make run` to run local PHP server

## Performance
- Performance is what matters here
  - API solution without any API security made from Nette with Presenters and Responses took over `80ms`, `20ms` when cached
  - This project implements simple security, DI injects into Endpoints and performs around `40ms`, `5ms` cached
  - **Measured by Tracy** - when debug mode is off I guess the app will perform even better

## Example
- See [sjiamnocna@renette](https://github.com/sjiamnocna/renette) for React + 

## Clone and enjoy
