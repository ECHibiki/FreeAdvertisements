# FreeAdvertising

Done with Test Driven Development on both React and Laravel PHP, this is a simple webapplication for hosting an advertisement platform with no monetization.<br/>
Uses Nginx, MySQL, Larvel, React and Docker(laradock). <br/>Testing done with PHPUnit, Jest+Enzyme.
<br/><br/>
Run on linux with 
```sudo docker-compose up -d mysql nginx && sudo docker-compose exec -u laradock workspace bash``` sudo prefix as required. Stop on existing ports.<br/>
Run PHP tests with ```phpunit```. Jest with ```npm run test```<br/>
