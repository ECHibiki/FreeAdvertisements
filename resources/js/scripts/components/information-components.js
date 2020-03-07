import React, { Component } from 'react';
import {DataStore, APICalls} from '../network/api';
import Popup from "reactjs-popup";
import { AdRemovalButton, ModRemovalButton, ModBanButton } from './form-components';
import {SampleBanner} from './image-components';

import {dimensions_w, dimensions_h} from '../settings'

export class HelperText extends Component{


	render(){
		
		// Trust in React to parse safely
		var code_string_iframe = '<iframe id="kissu-banner" src="/banner" scrolling="no" width="' + dimensions_w + '" height="' + dimensions_h + '" style="margin:auto;display:block;max-width:100%;border:none"></iframe>';
		var code_string_ajax = 	'<script>var fetch = new XMLHttpRequest();fetch.open("GET", "{{ config.banner_src }}api/banner");fetch.addEventListener("load", function(){var info = JSON.parse(this.responseText)[0];window.ban_url = document.createElement("A");window.ban_url.setAttribute("href", info["url"]);ban_url.setAttribute("style", "display:contents");var ban_img = document.createElement("IMG");ban_img.setAttribute("src", "{{ config.banner_src }}" + info["uri"]);ban_img.setAttribute("style", "margin:auto;display:block;max-width:100%;border:none;");	window.ban_url.appendChild(ban_img);if(document.getElementById("banner-container") != undefined);document.getElementById("banner-container").appendChild(window.ban_url);});fetch.send();</script>;'
		+ '\n\n<body><div id="banner-container"></div></body>';
		return (<div id="helper"><h2>How To Use</h2><p>To easily embed on a website use:<br/>			
					<textarea className="code" value={code_string_iframe} disabled=''/>
					A more sophisticated method is to preload the banner and then place into a container:<br/>
					<textarea className="code" value={code_string_ajax} disabled=''/>
					This is a slight bit faster than iframes and removes difficulties with resizing.
					<br/>Uploaded images must be {dimensions_w}x{dimensions_h} and safe for work

				</p>
			</div>);
	};
}
export class TopHeader extends Component{
	render(){
		return (<div id="top-head"><h1>Custom<br/>&emsp;Banners</h1></div>);
	}
}
export class AdDetailsTable extends Component{
	constructor(props){
		super(props);
		this.state = {row_data:[]}
	}
	
	JSXRowData(adData){
		var JSX_var = [];
		for(var index in adData){
			var entry = adData[index];
			entry['uri'] = entry['uri'].replace('public/image/', 'storage/image/');
			JSX_var.push(<AdDetailsEntry updateDetailsCallback={this.props.updateDetailsCallback} id={"banner-" + index} key={"banner-"+index} ad_src={entry['uri']} url={entry['url']}/>);
		}
		return JSX_var;
	}

	render(){
		return (<div id="details-table" className="table table-striped table-responsive">
			<table>
				<caption>ありがとうございます!</caption>		
				<thead className="thead-dark">
					<tr>
						<th className="ad-th-del">Remove</th>
						<th className="ad-th-img">Image</th>
						<th className="ad-th-url">URL</th>
					</tr>
				</thead>
				<tbody className="">
				{this.JSXRowData(this.props.adData)}
				</tbody>
			</table>
			</div>);
	}
}



export class AdDetailsEntry extends Component{
	constructor(props){
		super(props);
	}
	
	render(){
		return(<tr id={this.props.id} className="">
				<td className="ad-td-del"><AdRemovalButton updateDetailsCallback={this.props.updateDetailsCallback}  ad_src={this.props.ad_src} url={this.props.url} /></td>
				<td className="ad-td-img"><a href={this.props.ad_src} ><img src={this.props.ad_src}/></a></td>
				<td className="ad-td-url"><a href={this.props.url}>{this.props.url}</a></td>
			</tr>);
	}
}

export class ModDetailsTable extends Component{
	constructor(props){
		super(props);
		this.state = {row_data:[]}
	}
	
	JSXRowData(adData){
		var JSX_var = [];
		for(var index in adData){
			var entry = adData[index];
			entry['uri'] = entry['uri'].replace('public/image/', 'storage/image/');
			JSX_var.push(<ModDetailsEntry updateDetailsCallback={this.props.updateDetailsCallback} id={"banner-" + index} key={"banner-"+index} ad_src={entry['uri']} url={entry['url']} name={entry['fk_name']}/>);
		}
		return JSX_var;
	}

	render(){
		return (<div id="mod-details-table" className="table table-striped table-responsive">
			<table>
				<caption>ありがとうございます!</caption>		
				<thead className="thead-dark">
					<tr>
						<th className="ad-th-ban">Ban</th>
						<th className="ad-th-del">Delete</th>
						<th className="ad-th-name">Name</th>
						<th className="ad-th-img">Image</th>
						<th className="ad-th-url">URL</th>
					</tr>
				</thead>
				<tbody className="">
				{this.JSXRowData(this.props.adData)}
				</tbody>
			</table>
			</div>);
	}
}



export class ModDetailsEntry extends Component{
	constructor(props){
		super(props);
	}
	
	render(){
		return(<tr id={this.props.id} className="">
				<td className="ad-td-ban"><ModRemovalButton updateDetailsCallback={this.props.updateDetailsCallback} ad_src={this.props.ad_src} url={this.props.url} name={this.props.name}/></td>
				<td className="ad-td-del"><ModBanButton updateDetailsCallback={this.props.updateDetailsCallback} ad_src={this.props.ad_src} url={this.props.url} name={this.props.name}/></td>

				<td className="ad-td-name">{this.props.name}</td>
				<td className="ad-td-img"><a href={this.props.ad_src} ><img src={this.props.ad_src}/></a></td>
				<td className="ad-td-url"><a href={this.props.url}>{this.props.url}</a></td>
			</tr>);
	}
}
export class AllDetailsTable extends Component{
	constructor(props){
		super(props);
		this.state = {row_data:[]}
	}
	
	JSXRowData(adData){
		var JSX_var = [];
		for(var index in adData){
			var entry = adData[index];
			entry['uri'] = entry['uri'].replace('public/image/', 'storage/image/');
			JSX_var.push(<AllDetailsEntry updateDetailsCallback={this.props.updateDetailsCallback} 
				id={"banner-" + index} key={"banner-"+index} name={entry['fk_name']} ad_src={entry['uri']} url={entry['url']}/>);
		}
		// reverse ASC selector because DESC is messy
		return JSX_var;
	}

	render(){
		return (<div id="ad-details-table" className="table table-striped table-responsive">
			<table>
				<caption>ありがとうございます!</caption>		
				<thead className="thead-dark">
					<tr>
						<th className="ad-th-name">Name</th>
						<th className="ad-th-img">Image</th>
						<th className="ad-th-url">URL</th>
					</tr>
				</thead>
				<tbody className="">
				{this.JSXRowData(this.props.adData)}
				</tbody>
			</table>
			</div>);
	}
}
export class AllDetailsEntry extends Component{
	constructor(props){
		super(props);
	}
	
	render(){
		return(<tr id={this.props.id} className="">
				<td className="ad-td-name"><span className="ad-td-name-text">{this.props.name}</span></td>
				<td className="ad-td-img"><a href={this.props.ad_src} ><img src={this.props.ad_src}/></a></td>
				<td className="ad-td-url"><a href={this.props.url}>{this.props.url}</a></td>
			</tr>);
	}
}
