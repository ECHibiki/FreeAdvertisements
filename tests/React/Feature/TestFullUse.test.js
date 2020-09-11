import React from 'react';
import {render} from '@testing-library/react';
import Adapter from 'enzyme-adapter-react-16';
import { shallow, configure, mount } from 'enzyme';

configure({adapter: new Adapter()});
import { APICalls } from '../../../resources/js/components/api';
import { DataStore } from '../../../resources/js/components/api';

import { MasterPage } from '../../../resources/js/components/components';

describe("Main Page Design", function(){
	test("Home/login page layout", function(){
		const page = mount(<MasterPage />);
		expect(page).toMatchSnapshot()
	});
	test("user page layout", function(){
		
	});

});

describe("Button press form expansions", function(){
	test("SignIn button+form", function(){});
	test("CreateUser button+form", function(){});
	test("CreateAd button+form", function(){});
	test("RemoveAd button+form", function(){});
});

describe("Axios mocks for actions after button presses", function(){
	test("Creation => Sign in => user-page", function(){});
	test("Sign in => user-page", function(){});
	test("Add Ad", function(){});
	test("Generate Detail Page", function(){});
	test("Remove Ad", function(){});
});

