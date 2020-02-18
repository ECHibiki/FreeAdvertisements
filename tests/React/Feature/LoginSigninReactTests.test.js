import '@testing-library/jest-dom'
import React from "react";
import {render,fireEvent} from '@testing-library/react'

import {*} from './public/js/app'

//1.1 Account Sign In
test('Click Sign In', () => {
  expect(1 + 1).toBe(2)
});
//1.2
test('Sign In Name or Pass Fail', () => {
  expect(1 + 1).toBe(2)
});
//1.3
test('Sign In Success and Redirect', () => {
  expect(1 + 1).toBe(2)
});

//4.1 Account Creation
test('Click Create Account', () => {
  expect(1 + 1).toBe(2)
});
//4.2
test('Creation Duplicate Username', () => {
  expect(1 + 1).toBe(2)
});
//4.3
test('Creation Success and Redirect', () => {
  expect(1 + 1).toBe(2)
});
