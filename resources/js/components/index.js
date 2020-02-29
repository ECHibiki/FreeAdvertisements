import { MasterPage } from './components';
import ReactDOM from 'react-dom';
import React from 'react';

if(document.getElementById("index")){
	ReactDOM.render(<MasterPage />, document.getElementById("index"));
}

