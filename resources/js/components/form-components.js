import React, { Component } from 'react';
import {DataStore, APICalls} from './api';
import Popup from "reactjs-popup";

import {dimensions_w, dimensions_h} from './settings'

export class SignInButton extends Component{
	constructor(props){
		super(props);
	}

	render(){
		return (<div id="sign-in-start"><button onClick={this.props.onClickCallBack} type="button" className="btn btn-primary" >Sign In</button></div>);
	}

}
export class CreateButton extends Component{
	render(){
		return (<div id="create-start"><button onClick={this.props.onClickCallBack} type="button" className="btn btn-outline-dark" >New User</button></div>);
	}

}
export class AdCreateButton extends Component{
	render(){
		return (<div id="create-ad-start"><button onClick={this.props.onClickCallBack} type="button" className="btn btn-primary" >New Banner</button></div>);
	}

}

export class SignInForm extends Component{
	constructor(props){
		super(props);
	}
	render(){
		return(<div style={{visibility: this.props.visibility, opacity: this.props.opacity, height: this.props.height}} id="sign-form">
				<div className="form-group">
					<label htmlFor="name-si">UserName</label>
					<input className="form-control" id="name-si" placeholder="insert username" required/>
				</div>
				<div className="form-group">
					<label htmlFor="pass-si">Password</label>
					<input type="password" className="form-control" id="pass-si" placeholder="" required/>
				</div>
				<SignInAPIButton swapPage={this.props.swapPage}  />
			</div>);
	}
}
export class CreationForm extends Component{
	constructor(props){
		super(props);
	}
	render(){
		return(<div style={{visibility: this.props.visibility, opacity:this.props.opacity, height: this.props.height}} id="create-form">
				<div className="form-group">
					<label htmlFor="name-c">UserName</label>
					<input className="form-control" id="name-c" placeholder="insert username" required/>
				</div>
				<div className="form-group">
					<label htmlFor="pass-c">Password</label>
					<input type="password" className="form-control" id="pass-c" placeholder="5 character min" required/>
				</div>
				<div className="form-group">
					<label htmlFor="pass-c-conf">Confirm Password</label>
					<input type="password" className="form-control" id="pass-c-conf" placeholder="confirmation" required/>
				</div>

				<CreateAPIButton swapPage={this.props.swapPage}  />
			</div>);
	}

}
export class AdCreationForm extends Component{
	constructor(props){
		super(props);
	}
	render(){
		return(<div style={{visibility: this.props.visibility, height: this.props.height, opacity: this.props.opacity}} id="ad-create-form">
				<div className="form-group">
					<label htmlFor="image-ad-c">Image</label>
					<input type="file" className="form-control-file" id="image-ad-c" accept="image/*" required/>
					<small className="form-text text-muted">Must be { dimensions_w }x{ dimensions_h } and SFW</small>
				</div>
				<div className="form-group">
					<label htmlFor="ad-url-c">URL</label>
					<input type="url" pattern="/^http(|s):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]+\.[A-Z0-9+&@#\/%=~_|]+$/i" className="form-control" id="ad-url-c" placeholder="http/https urls only" required/>
				</div>

				<AdCreateAPIButton UpdateDetails={this.props.UpdateDetails}/>
			</div>);
	}

}
export class AdRemovalForm extends Component{
	constructor(props){
		super(props);
		this.state = {display: "none", height: "10em"};
	}
	render(){
		return(<div style={{display: this.state.display, height: this.state.height}} id="ad-create-form">
			<form>
				<input type="hidden" id="r-uri" required/>
				<input type="hidden" id="r-url" required/>
				<small className="form-text text-muted text-danger">Confirm Delete for this Ad</small>
				<AdRemovalAPIButton />
			</form></div>);
	}

}
export class SignInAPIButton extends Component{
	constructor(props){
		super(props);
		this.SendUserSignIn = this.SendUserSignIn.bind(this);
		this.state = {err_text:"", war_text:"", suc_tex:""};

	}

	async SendUserSignIn(e){
		var name = document.getElementById("name-si").value;
		var pass = document.getElementById("pass-si").value;

		var si_response = await APICalls.callSignIn(name, pass);
		if("message" in si_response){
			if("errors" in si_response){
				var reasons_arr = []
				for(var reason in si_response['errors']){
					reasons_arr.push(si_response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({err_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ), war_text: "", suc_text:""});
			}
			else{
				this.setState({err_text:<span>Authorization Failed, Please Refresh<br/></span>});
			}
		}
		else if("warn" in si_response){
			this.setState({war_text:si_response['warn']});
		}
		else{
			DataStore.storeAuthToken(si_response['access_token']);
			this.props.swapPage();
		}
	}

	setDisplay(r){
		if(r == ""){
			return {display:"none"}
		}
		else{
			return {display:"block"}
		}
	}

	render(){
		return (<div id="sign-in-finish"><button type="button" className="btn btn-secondary" onClick={this.SendUserSignIn}>Submit</button>
			<p className="text-danger" id="si-error-field" style={this.setDisplay(this.state.err_text)}>{this.state.err_text}</p>
			<p className="text-warning bg-dark" id="si-war-field" style={this.setDisplay(this.state.war_text)}><span className="bg-dark">{this.state.war_text}</span></p>
			<p className="text-success" id="si-win-field" style={this.setDisplay(this.state.suc_text)}>{this.state.suc_text}</p>
			</div>);
	}

}
export class CreateAPIButton extends Component{
	constructor(props){
		super(props);
		this.SendUserCreate = this.SendUserCreate.bind(this);
		this.state = {err_text:"", war_text:"", suc_tex:""};
	}

	async SendUserCreate(e){
		var name = document.getElementById("name-c").value;
		var pass = document.getElementById("pass-c").value;
		var pass_confirmation = document.getElementById("pass-c-conf").value;
		var response = await APICalls.callCreate(name, pass, pass_confirmation);
		if("message" in response){
			if("errors" in response){
				var reasons_arr = []
				for(var reason in response['errors']){
					reasons_arr.push(response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({err_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ), war_text: "", suc_text:""});
			}
		}
		else if("warn" in response){
			this.setState({war_text:response['warn'], suc_text: "", err_text:""});
		}
		else{
			this.setState({suc_text:response['log'], war_text: "", err_text:""});
			var si_response = await APICalls.callSignIn(name, pass);
			if("message" in si_response){
				if("errors" in si_response){
					var reasons_arr = []
					for(var reason in si_response['errors']){
						reasons_arr.push(si_response['errors'][reason]);
					}
					var key_ind = 0;
					this.setState({err_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ), war_text: "", suc_text:""});
				}
				else{
					this.setState({err_text:<span>Authorization Failed, Please Refresh<br/></span>});
				}
			}
			else if("warn" in si_response){
				this.setState({war_text:si_response['warn']});
			}
			else{
				DataStore.storeAuthToken(si_response['access_token']);
				this.props.swapPage();
			}
		}
	}

	setDisplay(r){
		if(r == ""){
			return {display:"none"}
		}
		else{
			return {display:"block"}
		}
	}


	render(){
		return (<div id="create-finish"><button type="button" className="btn btn-secondary" onClick={this.SendUserCreate}>Create</button>
			<p className="text-danger" id="c-error-field" style={this.setDisplay(this.state.err_text)}>{this.state.err_text}</p>
			<p className="text-warning bg-dark" id="c-war-field" style={this.setDisplay(this.state.war_text)}><span className="bg-dark">{this.state.war_text}</span></p>
			<p className="text-success" id="c-win-field" style={this.setDisplay(this.state.suc_text)}>{this.state.suc_text}</p>
			</div>);
	}

}
export class AdCreateAPIButton extends Component{
	constructor(props){
		super(props);
		this.SendNewBanner = this.SendNewBanner.bind(this);
		this.state = {err_text:"", war_text:"", suc_tex:""};
	}

	async SendNewBanner(e){
		var image = document.getElementById("image-ad-c").files[0];
		var url = document.getElementById("ad-url-c").value;
		var nb_response = await APICalls.callCreateNewAd(image, url);
		if("message" in nb_response){
			if("errors" in nb_response){
				var reasons_arr = []
				for(var reason in nb_response['errors']){
					reasons_arr.push(nb_response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({err_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ), war_text: "", suc_text:""});
			}
			else if(nb_response['code'] == "413"){
				this.setState({err_text:<span>Upload Failed. File too Large<br/></span>});

			}
			else{
				this.setState({err_text:<span>Authorization Failed, Please Refresh<br/></span>});
			}
		}
		else if("warn" in nb_response){
			this.setState({war_text:nb_response['warn']});
		}
		else{
			this.props.UpdateDetails();
		}
	}


	render(){
		return (<div id="ad-create-finish"><button type="button" className="btn btn-secondary" onClick={this.SendNewBanner}>Create</button>
			<p className="text-danger" id="cad-error-field">{this.state.err_text}</p>
			<p className="text-warning bg-dark" id="cad-war-field"><span className="bg-dark">{this.state.war_text}</span></p>
			<p className="text-success" id="cad-win-field">{this.state.suc_text}</p>
			</div>);
	}

}
export class AdRemovalButton extends Component{
	constructor(props){
		super(props);
	}

	render(){
		return (<div id="ad-remove">
			<Popup trigger={<button type="button" className="btn btn-danger btn-sm">Remove</button>}>
			{close => (
				<div>
				<p className="text-danger">Are you sure you want to delete this?</p>
				<AdRemovalAPIButton updateDetailsCallback={this.props.updateDetailsCallback}  onClickCallBack={close} ad_src={this.props.ad_src} url={this.props.url}/>
				</div>
			)}
			</Popup></div>);
	}
}
export class AdRemovalAPIButton extends Component{
	constructor(props){
		super(props);
		this.state = {err_text:"", war_text:"", suc_tex:""};

		this.RemoveAd = this.RemoveAd.bind(this);
	}

	async RemoveAd(){
		const uri=this.props.ad_src;
		const url= this.props.url;

		var rem_response = await APICalls.callRemoveUserAds(uri,url);
		if("message" in rem_response){
			if("errors" in rem_response){
				var reasons_arr = []
				for(var reason in rem_response['errors']){
					reasons_arr.push(rem_response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({err_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ), war_text: "", suc_text:""});
			}
			else{
				this.setState({err_text:<span>Authorization Failed, Please Refresh<br/></span>});
			}
		}
		else if("warn" in rem_response){
			this.setState({war_text:rem_response['warn']});
		}
		else{
			this.props.updateDetailsCallback();
			this.props.onClickCallBack();

		}
	}

	render(){
		return (<div id="ad-remove"><button type="button" className="btn btn-danger btn-sm" onClick={this.RemoveAd}>Remove</button>
			<br/><p className="text-danger" id="cr-error-field">{this.state.err_text}</p>
			<p className="text-warning bg-dark" id="cr-war-field"><span className="bg-dark">{this.state.war_text}</span></p>
			<p className="text-success" id="cr-win-field">{this.state.suc_text}</p>
			</div>);
	}
}

