import React, { Component } from 'react';
import {DataStore, APICalls} from './api';
import Popup from "reactjs-popup";

import {dimensions_w, dimensions_h} from './settings'

export class PatreonBanner extends Component{
	render(){
		return (<div id="patreon"><a href="https://patreon.com/ECVerniy"><img src="https://abdullahsameer-8c5e.kxcdn.com/blog/wp-content/uploads/2018/11/support-my-work-on-patreon-banner-image-600px.png"/></a></div>);
	}

}
export class SampleBanner extends Component{
	render(){
		return (<div id="sample-banner"><iframe src="/banner" scrolling="no" width={dimensions_w} height={dimensions_h} className="iframe-banner"></iframe></div>);
	}
}
