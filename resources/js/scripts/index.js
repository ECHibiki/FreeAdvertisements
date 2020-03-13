import { MasterPage, AllPage, ModPage } from './components/container-components';
import { FooterInfo } from './components/information-components';
import ReactDOM from 'react-dom';
import React from 'react';

import {
  BrowserRouter as Router,
  Switch,
  Route,
  Link,
  useRouteMatch,
  useParams
} from "react-router-dom";

if(document.getElementById("index")){
	ReactDOM.render(
		<Router>
		 <Switch>
		  <Route path="/all" component={AllPage} />
		  <Route path="/mod" component={ModPage} />
		  <Route path="/" component={MasterPage} />
		 </Switch>
		</Router>,
	document.getElementById("index"));
}
if(document.getElementById("footer")){
	ReactDOM.render(
		<FooterInfo />,
	document.getElementById("footer"));
}

