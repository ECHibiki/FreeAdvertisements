# FreeAdvertising

Done initially with Test Driven Development on both React and Laravel PHP, this is a simple webapplication for hosting an advertisement platform with no monetization.<br/>
Uses Nginx, MySQL, Larvel and React ~and Docker(laradock)~. Docker is a consideration. <br/>Testing done with PHPUnit, Jest+Enzyme. 
<br/>

## Configuration

If you want to host your own version you must:
1) Pay attention to the donator page: https://www.patreon.com/ECHibiki
2) In resources/js/components/api.js configure host_addr and host_name to work with your site
3) Adjust app/http/controllers/ConfidentialInfoController.php to have the image dimensions desired. In the components.js change where desired.

Unit and integration tests won't all pass, but functions as intended from lots of manual testing. Will be improved upon.
<hr />
<a href="https://www.patreon.com/ECHibiki"><img src="https://i2.wp.com/arledgecomics.com/wp-content/uploads/2017/03/support-my-work-on-patreon-banner-image-600px.png?resize=600%2C208&ssl=1" />
</a>
