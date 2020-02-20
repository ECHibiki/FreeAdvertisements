import '@testing-library/jest-dom'
import React from "react";
import {render,fireEvent} from '@testing-library/react'
import Enzyme, { shallow, render, mount } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';

import SignInInterface from './public/js/SignInInterface';
import CreateAccountInterface from './public/js/CreateAccountInterface';
import MainPage from './public/js/MainPage';
import HomePage from './public/js/HomePage';

import SignInButton from './public/js/SignInAction';
import CreateAccountButton from './public/js/CreateAccountAction';
import SignInForm from './public/js/SignInForm';
import CreateAccountForm from './public/js/SignInForm';


//0.0 Home
test('Home Page Exists', () => {
  const home = shallow(
  	<HomePage />
  );
  expect(home).toMatchSnapshot();
});

//1.1 Account Sign In
test('Click Sign In', () => {
  const sign = mount(
  	<SignInterface />
  );
  const sign_button = sign.find("SignInButton");
  sign_button.find("div").first().simulate("click");	
  expect().toMatchSnapshot();
});
//1.2
test('Sign In Name or Pass Fail', () => {
  const form = mount(
  	<SignInForm />
  );
  form.find("div").first().simulate("click");	
  expect().toMatchSnapshot();

});
//1.3
test('Sign In Success and Redirect', () => {
  const form = mount(
  	<MainPage />
  );
  const sign_button = form.find("SignInButton");
  sign_button.find("div").first().simulate("click");	

  form.find("SignInForm").first().find("NameInput").setProps({val, "testername"});
  form.find("SignInForm").first().find("PassInput").setProps({val, "testerpass"});
  form.find("SignInForm").first().find("SignInSubmit").find("div").simulate("click");	
  expect().toMatchSnapshot();

});

//4.1 Account Creation
test('Click Create Account', () => {
  const create = mount(
  	<CreateAccountInterface />
  );
  const create_button = sign.find("CreateAccountButton");
  sign_button.find("div").first().simulate("click");	
  expect().toMatchSnapshot();

});
//4.2
test('Creation Duplicate Username', () => {
  const form = mount(
  	<MainPage />
  );
  const create_button = sign.find("CreateAccountButton");
  sign_button.find("div").first().simulate("click");	

  form.find("CreateAccountForm").first().find("NameInput").setProps({val, "testername"});
  form.find("CreateAccountForm").first().find("PassInput").setProps({val, "testerpass"});
  form.find("CreateAccountForm").first().find("CreateAccountSubmit").find("div").simulate("click");	
  expect().toMatchSnapshot();
});
//4.3
test('Creation Success and Redirect', () => {
  const form = mount(
  	<MainPage />
  );
  const create_button = sign.find("CreateAccountButton");
  sign_button.find("div").first().simulate("click");	

  form.find("CreateAccountForm").first().find("NameInput").setProps({val, "" + Math.floor(Math.random())});
  form.find("CreateAccountForm").first().find("PassInput").setProps({val, "" + Math.floor(Math.random())});
  form.find("CreateAccountForm").first().find("div").simulate("click");	
  expect().toMatchSnapshot();
});
