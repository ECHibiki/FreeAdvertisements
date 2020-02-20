import '@testing-library/jest-dom'
import React from "react";
import {render,fireEvent} from '@testing-library/react'
import Enzyme, { shallow, render, mount } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';

import AdvertisementDisplay from './public/js/AdvertisementDisplay';
import UserPage from './public/js/UserPage';

import AdCreateInterface from './public/js/AdCreateInterface';
import AdCreateButton from './public/js/AdCreateButton';
import AdCreateForm from './public/js/AdCreateForm';

//7.1 User Page
test('Generic User Page Exists', () => {
  //a non specified user will produce generic test results with nothing specific
  const user = shallow(
  	<UserPage />
  );
  expect(user).toMatchSnapshot();

});
//7.2
test('User Specific Info Retrieved', () => {
  // retrieve data on a character
  const user = shallow(
  	<UserPage />
  );
  user.setState({user: "testername", ses_id: "validtoken"});
  expect(user).toMatchSnapshot();

});
//7.3
test('User Specific Info Fails', () => {
  // retrieve data on a character
  const ads = shallow(
  	<AdvertisementDisplay />
  );
  ads.setState({user: "testername", ses_id: "invalidtoken"});
  expect(user).toMatchSnapshot();

});

//7.4
test('Ad Creation Clicked', () => {
  const ad_int = shallow(<AdCreateInterface />);
  ad_int.find("AdCreateButton").first().find("div").simulate("click");
  expect().toMatchSnapshot();
});
//7.5
test('Ad Creation Success', () => {
  expect(1 + 1).toBe(6)
 
	// need good way to fake file uploads
	// probably will do without tests
});
//7.6
test('Ad Creation Fail', () => {
  const form = shallow(<AdCreateForm />);
  form.find("CreationSubmit").find("div").simulate("click");	
  expect().toMatchSnapshot();
});

