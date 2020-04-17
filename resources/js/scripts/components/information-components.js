import React, { Component } from 'react';
import {DataStore, APICalls} from '../network/api';
import Popup from "reactjs-popup";
import { AdRemovalButton, ModRemovalButton, ModBanButton } from './form-components';
import {SampleBanner} from './image-components';

import {dimensions_w, dimensions_h, dimensions_small_w,dimensions_small_h} from '../settings'

export class HelperText extends Component{


	render(){

		// Trust in React to parse safely
		var code_string_iframe = '<iframe id="kissu-banner" src="/banner" scrolling="no" width="' + dimensions_w + '" height="' + dimensions_h + '" style="margin:auto;display:block;max-width:100%;border:none"></iframe>';
		var code_string_ajax = 	'<script>var fetch = new XMLHttpRequest();fetch.open("GET", "{{ config.banner_src }}api/banner");fetch.addEventListener("load", function(){var info = JSON.parse(this.responseText)[0];window.ban_url = document.createElement("A");window.ban_url.setAttribute("href", info["url"]);ban_url.setAttribute("style", "display:contents");var ban_img = document.createElement("IMG");ban_img.setAttribute("src", "{{ config.banner_src }}" + info["uri"]);ban_img.setAttribute("style", "margin:auto;display:block;max-width:100%;border:none;");	window.ban_url.appendChild(ban_img);if(document.getElementById("banner-container") != undefined);document.getElementById("banner-container").appendChild(window.ban_url);});fetch.send();</script>;'
		+ '\n\n<body><div id="banner-container"></div></body>';
		return (<div id="helper"><h2>How To Use</h2><p>To easily embed on a website use:<br/>
					<textarea className="code" value={code_string_iframe} readOnly/>
					A more sophisticated method is to preload the banner and then place into a container:<br/>
					<textarea className="code" value={code_string_ajax} readOnly/>
					This is a slight bit faster than iframes and removes difficulties with resizing.
					<br/>Uploaded images must be {dimensions_w}x{dimensions_h} or {dimensions_small_w}x{dimensions_small_h} and safe for work

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
			JSX_var.push(<ModDetailsEntry updateDetailsCallback={this.props.updateDetailsCallback} id={"banner-" + index} key={"banner-"+index} ad_src={entry['uri']} url={entry['url']} name={entry['fk_name']} ban={entry['hardban']}/>);
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
						<th className="ad-th-ban">Ban State</th>
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
		var ban_str = "-";;
		if(this.props.ban == 1){
			var ban_str =  "Hardban";
		}
		else if(this.props.ban == 0){
			var ban_str = "Softban";
		}
		return(<tr id={this.props.id} className="">
				<td className="ad-td-ban"><ModRemovalButton updateDetailsCallback={this.props.updateDetailsCallback} ad_src={this.props.ad_src} url={this.props.url} name={this.props.name}/></td>
				<td className="ad-td-del"><ModBanButton updateDetailsCallback={this.props.updateDetailsCallback} ad_src={this.props.ad_src} url={this.props.url} name={this.props.name}/></td>

				<td className="ad-td-name">{this.props.name}</td>
				<td className="ad-td-img"><a href={this.props.ad_src} ><img src={this.props.ad_src}/></a></td>
				<td className="ad-td-url"><a href={this.props.url}>{this.props.url}</a></td>
				<td className="ad-td-ban">{ban_str}</td>
			</tr>);
	}
}
export class AllDetailsTable extends Component{

	constructor(props){
		super(props);
		this.state = {row_data:[]}
	}

	JSXRowData(adData_const){
		var adData = [ ...adData_const];

		var JSX_var = [];
		//sort it
		if(this.props.sortingDetails == "none"){}
		else{
			for(var index_i = 0; index_i < adData.length; index_i++){
				for(var index_j = 0; index_j < adData.length; index_j++){
					if(adData[index_i]['clicks'] > adData[index_j]['clicks']){
						let ad_tmp = adData[index_i];
						adData[index_i] = adData[index_j];
					  adData[index_j] = ad_tmp;
					}
				}
			}
		}
		if(this.props.sortingDetails == "asc"){
			adData = adData.reverse();
		}
		else{}

		//asign it
		for(var index in adData){
			var entry = adData[index];
			if(this.props.filterDetails == "none" || entry['size'] == this.props.filterDetails){
				entry['uri'] = entry['uri'].replace('public/image/', 'storage/image/');
				JSX_var.push(<AllDetailsEntry updateDetailsCallback={this.props.updateDetailsCallback}
					id={"banner-" + index} key={"banner-"+index} name={entry['fk_name']} ad_src={entry['uri']} url={entry['url']} click_count={entry['clicks']}/>);
			}
		}
		return JSX_var;
	}

	render(){
		const row_data = this.props.adData;
		return (<div id="ad-details-table" className="table table-striped table-responsive">
			<table>
				<caption>ありがとうございます!</caption>
				<thead className="thead-dark">
					<tr>
						<th className="ad-th-name">Name</th>
						<th className="ad-th-img">Image</th>
						<th className="ad-th-url">URL</th>
						<th className="ad-th-clicks">Clicks</th>
					</tr>
				</thead>
				<tbody className="" data-sorting={this.props.sortingDetails}>
					{this.JSXRowData(row_data)}
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
				<td className="ad-td-clicks"><span className="ad-td-clicks-text">{this.props.click_count}</span></td>
			</tr>);
	}
}
export class DonatorBox extends Component{
	render(){
		var html = {__html: process.env.MIX_EXTRA_INFO}
		return(<div id="donation" dangerouslySetInnerHTML={html}></div>);
	}
}

export class FooterInfo extends Component{
	render(){
		return(<div id='footer'><a href="https://github.com/ECHibiki/Community-Banners">Community Banners - {process.env.MIX_VERSION_NO}</a><br/> Verniy - MPL-2.0, 2020<br/>Concerns should be sent to Verniy @ <a href="https://kissu.moe/b/res/2275">kissu.moe</a></div>);
	}
}
