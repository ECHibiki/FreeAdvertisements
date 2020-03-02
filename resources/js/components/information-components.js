import React, { Component } from 'react';
import {DataStore, APICalls} from './api';
import Popup from "reactjs-popup";
import { AdRemovalButton } from './form-components'

import {dimensions_w, dimensions_h} from './settings'

export class HelperText extends Component{
	render(){
		return (<div id="helper">
		<h2>How To Use</h2>
		<p>To embed on a website use <span className="code">{
			'<iframe id="kissu-banner" src="https://banners.kissu.moe/banner" scrolling="no" width="'}{dimensions_w}{'" height="'}{dimensions_h}{'" style="margin:auto;display:block;max-width:100%;border:none"></iframe>'
		}</span>. 
			Uploaded images must be {dimensions_w}x{dimensions_h} and safe for work</p>
		</div>)
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
				<td className="ad-td-del"><AdRemovalButton updateDetailsCallback={this.props.updateDetailsCallback}  ad_src={this.props.ad_src} url={this.props.url}/></td>
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
		return JSX_var;
	}

	render(){
		return (<div id="details-table" className="table table-striped table-responsive">
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
