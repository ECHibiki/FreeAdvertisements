import { APICalls } from '../../../resources/js/components/api';
import { InMemoryLocalStore } from '../../../resources/js/components/api';
import axios from 'axios';
import polyfill from "babel-polyfill";

const adapter = require('axios/lib/adapters/http')

describe("Aux API functions", function(){
	test("Client Side Password Hashing", function(){
		expect(APICalls.hashPass("test", "pass")).toBe("21b3dc15bcbaa285b2a9ca5254ca0ae8a18e660e6e55bce163f4b9f893df4704");
		expect(APICalls.hashPass("zest", "ssap")).toBe("07cd5364c7426eb6cd596d128ee92ed84a40a2a0c70aea033be24d066343090f");
	});	
	test("Token stored and get", function(){
		InMemoryLocalStore.storeAuthToken("token");
		expect(InMemoryLocalStore.getAuthToken()).toBe("token");
		expect(window.localStorage.getItem("freeadstoken")).toBe("token");
	});
	test("encrypted uname:pwrd stored", function(){
		InMemoryLocalStore.storeConfidential("test", "pass");
		expect(InMemoryLocalStore.getConfidential()).toEqual({"name":"test", "pass":"21b3dc15bcbaa285b2a9ca5254ca0ae8a18e660e6e55bce163f4b9f893df4704"});
		expect(JSON.parse(window.localStorage.getItem("freeadsconfidential"))).toEqual({"name":"test", "pass":"21b3dc15bcbaa285b2a9ca5254ca0ae8a18e660e6e55bce163f4b9f893df4704"});

	});
});
/*
describe("API functionality utests", function(){
	test("Creation of user", async ()=>{
		expect.assertions(1);
		var create_re = await APICalls.callCreate("test" + Math.ceil(Math.random() * 1000), "2xhashpass");
		expect(create_re).toEqual({'log':'1'});
	});	
	test("Failed Creation of user", function(){
		expect.assertions(1);
		return expect(APICalls.callCreate("test123"), "2xhashpass").resolves.toEqual({'warn':'0'});
	});	
	test("Sign in of User", function(){
		var log = APICalls.callSignIn("preset", "2xhashpass")['log'];
		expect(log).toBe("1");

		expect(APICalls.callSignIn("preset", "2xhashpass")).toEqual({'log':'0'});

		expect(APICalls.callSignIn("preset", "")).toBe("");

	});




	test("Creation of a new Ad", function(){
		// empty variable fail test only
		// must also insert token for testing
		var token = "";
		expect(APICalls.callCreateNewAd(null, "https://test.com", token)).toBe("");
	});	
	test("Retrieval of User Ads", function(){
		//must insert token for testing
		var token = "";
		expect(APICalls.callRetrieveUserAds(token)).toEqual("");
	});	
	test("Removal of an Ad no token", function(){
		expect.assertions(1);
		// Will write tests if needed
		var token = ""
		expect(APICalls.callRemoveUserAds(null, "https://test.com", token)).toBe("");
	});	
	test("Removal of an Ad", function(){
		expect.assertions(1);
		// Will write tests if needed
		// must insert token for testings
		var token = ""
		expect(APICalls.callRemoveUserAds(null, "https://test.com", token)).toBe("");
	});
});
*/


