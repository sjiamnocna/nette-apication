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
- Automatically processes data from [JS/Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API), that came via `STDIN` if `x-requested-with: XMLHttpRequest` header is present
- Use Tracy to display error over XHR/Fetch requests
- Introduces minimalistic AJAX API security
- Using [git@github.com:sjiamnocna/renette.git](https://github.com/sjiamnocna/renette) as frontend you can simplify React + Nette integration
    - if you clone this repo into `server/` directory within the project, you can use `npm start` or `yarn start` to run React (Node) and PHP dev server (**DEV only, don\'t use on public**)
    - more features coming
- If you dont like anything, feel free to override

## Dont get me wrong, I love Nette!
Nette, Latte etc. are great. It's IMHO just not efficient enough for modern "API" world

## Usage
- Do `composer require sjiamnocna/nette-apication` or add `sjiamnocna/nette-apication` to your composer.json and run `composer install`
- Create `temp` and `log` directories for Nette to work
- Add service to configuration `app/config/common.neon`
```
services:
	Application: APIcation\CApplication(%parameters%)
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

## Code
- Create your Endpoint class starting with 'E' (like `EEndpoint`) that inherits from CAbstractEndpoint.
  - I suggest you create `src/Endpoints/` directory for your Endpoints
  - Feel free to override public method `run()` that runs anytime the Endpoint is called if you find out you need to (`be or not to be` a performance benefit), but then you're on your own :)
- There, create a method `default` (or `__default` for secret method)
- Access `apidomain.com/api/endpoint` URL

## Performance
- Performance is what matters here
  - API solution made from basic Nette-app with Presenters and Responses without any API security took over `80ms`, `20ms` when cached
  - This project implements simple security, DI injects into Endpoints and performs around `40ms`, `5ms` cached
  - **Measured by Tracy** - when debug mode is off I guess the app will perform even better

## Example
- See [sjiamnocna@renette](https://github.com/sjiamnocna/renette) for React + Nette

## Install and enjoy
