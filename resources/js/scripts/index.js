import { MasterPage, AllPage } from './components/container-components';
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
		  <Route path="/" component={MasterPage} />
		 </Switch>
		</Router>,
	document.getElementById("index"));
}

