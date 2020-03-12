import React, { Component } from 'react';
import {DataStore, APICalls} from '../network/api';
import Popup from "reactjs-popup";

import {dimensions_w, dimensions_h} from '../settings'

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
		this.state = {file_input: "", url_input: ""}

		this.unsetFormFields = this.unsetFormFields.bind(this);
		this.handleFileChange = this.handleFileChange.bind(this);
		this.handleURLChange = this.handleURLChange.bind(this);

	}

	unsetFormFields(){
		this.setState({file_input:"", url_input: ""});
	}

	handleFileChange(e){
		this.setState({file_input:e.target.value});
	}
	handleURLChange(e){
		this.setState({url_input:e.target.value});
	}

	render(){
		return(<div style={{visibility: this.props.visibility, height: this.props.height, opacity: this.props.opacity}} id="ad-create-form">
				<div className="form-group">
					<label htmlFor="image-ad-c">Image</label>
					<input onChange={this.handleFileChange} value={this.state.file_input}  type="file" className="form-control-file" id="image-ad-c" accept="image/*" />
					<small  className="form-text text-muted">Must be { dimensions_w }x{ dimensions_h } and SFW</small>
				</div>
				<div className="form-group">
					<label htmlFor="ad-url-c">URL</label>
					<input onChange={this.handleURLChange} value={this.state.url_input} type="url" pattern="/^http(|s):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]+\.[A-Z0-9+&@#\/%=~_|]+$/i" className="form-control" id="ad-url-c" placeholder="http/https urls only" />
				</div>

				<AdCreateAPIButton UnsetFormFields={this.unsetFormFields} UpdateDetails={this.props.UpdateDetails}/>
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
		this.state = {info_text:"", info_class:""};
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
				this.setState({
					info_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ),
					info_class:"text-danger" 
				});
			}
			else{
				this.setState({
					info_text:<span>Authorization Failed, Please Refresh<br/></span>,
					info_class:"text-danger" 
				});
			}
		}
		else if("warn" in si_response){
			this.setState({info_text:si_response['warn'], info_class:"text-warning bg-dark"});
		}
		else{
			this.setState({info_text:si_response['log'], info_class:"text-success"});
			DataStore.storeAuthToken(si_response['access_token']);
			this.props.swapPage();
		}
	}

	render(){
		return (<div id="sign-in-finish"><button type="button" className="btn btn-secondary" onClick={this.SendUserSignIn}>Submit</button>
			<p className={this.state.info_class}  id="si-info-field" >{this.state.info_text}</p>
			</div>);
	}

}
export class CreateAPIButton extends Component{
	constructor(props){
		super(props);
		this.SendUserCreate = this.SendUserCreate.bind(this);
		this.state = {info_text:"", info_class:""};
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
				this.setState({
					info_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ),
					info_class:"text-danger" 
				});
			}
			else{
				this.setState({
					info_text:<span>Authorization Failed, Please Refresh<br/></span>,
					info_class:"text-danger" 
				});
			}
		}
		else if("warn" in response){
			this.setState({info_text:response['warn'], info_class:"text-warning bg-dark"});
		}
		else{
			this.setState({info_text:response['log'], info_class:"text-success"});
			var response = await APICalls.callSignIn(name, pass);
			if("message" in response){
				if("errors" in response){
					var reasons_arr = []
					for(var reason in response['errors']){
						reasons_arr.push(response['errors'][reason]);
					}
					var key_ind = 0;
					this.setState({
						info_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ),
						info_class:"text-danger" 
					});
				}
				else{
					this.setState({
						info_text:<span>Authorization Failed, Please Refresh<br/></span>,
						info_class:"text-danger" 
					});
				}
			}
			else if("warn" in response){
				this.setState({info_text:response['warn'], info_class:"text-warning bg-dark"});
			}
			else{
				this.setState({info_text:response['log'], info_class:"text-success"});
				DataStore.storeAuthToken(response['access_token']);
				this.props.swapPage();
			}
		}
	}

	render(){
		return (<div id="create-finish"><button type="button" className="btn btn-secondary" onClick={this.SendUserCreate}>Create</button>
			<p className={this.state.info_class}  id="c-info-field" >{this.state.info_text}</p>
			</div>);
	}

}
export class AdCreateAPIButton extends Component{
	constructor(props){
		super(props);
		this.SendNewBanner = this.SendNewBanner.bind(this);
		this.state = {info_text:"", info_class:""};
	}

	async SendNewBanner(e){
		var image = document.getElementById("image-ad-c").files[0];
		var url = document.getElementById("ad-url-c").value;
		var response = await APICalls.callCreateNewAd(image, url);
		if("message" in response){
			if("errors" in response){
				var reasons_arr = []
				for(var reason in response['errors']){
					reasons_arr.push(response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({
					info_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ),
					info_class:"text-danger" 
				});
			}
			else{
				this.setState({
					info_text:<span>Authorization Failed, Please Refresh<br/></span>,
					info_class:"text-danger" 
				});
			}
		}
		else if("warn" in response){
			this.setState({info_text:response['warn'], info_class:"text-warning bg-dark"});
		}
		else{
			this.setState({info_text:response['log'], info_class:"text-success"});
			this.props.UpdateDetails();
			this.props.UnsetFormFields();
		}
	}


	render(){
		return (<div id="ad-create-finish"><button type="button" className="btn btn-secondary" onClick={this.SendNewBanner}>Create</button>
			<p className={this.state.info_class}  id="cad-info-field" >{this.state.info_text}</p>
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
export class ModRemovalButton extends Component{
	constructor(props){
		super(props);
	}

	render(){
		return (<div id="ad-remove">
			<Popup trigger={<button type="button" className="btn btn-danger btn-sm">Remove</button>}>
			{close => (
				<div>
				<p className="text-danger">Delete all by name or delete individual?</p>
				<ModIndividualRemovalAPIButton updateDetailsCallback={this.props.updateDetailsCallback}  onClickCallBack={close} ad_src={this.props.ad_src} url={this.props.url} name={this.props.name}/>&nbsp;
				<ModCompleteRemovalAPIButton updateDetailsCallback={this.props.updateDetailsCallback}  onClickCallBack={close} ad_src={this.props.ad_src} url={this.props.url} name={this.props.name}/>

				</div>
			)}
			</Popup></div>);
	}
}
export class ModBanButton extends Component{
	constructor(props){
		super(props);
	}

	render(){
		return (<div id="ad-remove">
			<Popup trigger={<button type="button" className="btn btn-info btn-sm">Ban</button>}>
			{close => (
				<div>
				<p className="text-info">Shadow realm this user or hard ban user?</p>
				<ModSoftBanAPIButton updateDetailsCallback={this.props.updateDetailsCallback}  onClickCallBack={close} ad_src={this.props.ad_src} url={this.props.url} name={this.props.name}/>
				<ModHardBanAPIButton updateDetailsCallback={this.props.updateDetailsCallback}  onClickCallBack={close} ad_src={this.props.ad_src} url={this.props.url} name={this.props.name}/>
				</div>
			)}
			</Popup></div>);
	}
}

export class AdRemovalAPIButton extends Component{
	constructor(props){
		super(props);
		this.state = {info_text:"", info_class:""};

		this.RemoveAd = this.RemoveAd.bind(this);
	}

	async RemoveAd(){
		const uri=this.props.ad_src;
		const url= this.props.url;

		var response = await APICalls.callRemoveUserAds(uri,url);
		if("message" in response){
			if("errors" in response){
				var reasons_arr = []
				for(var reason in response['errors']){
					reasons_arr.push(response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({
					info_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ),
					info_class:"text-danger" 
				});
			}
			else{
				this.setState({
					info_text:<span>Authorization Failed, Please Refresh<br/></span>,
					info_class:"text-danger" 
				});
			}
		}
		else if("warn" in response){
			this.setState({info_text:response['warn'], info_class:"text-warning bg-dark"});
		}
		else{
			this.setState({info_text:response['log'], info_class:"text-success"});
			this.props.updateDetailsCallback();
			this.props.onClickCallBack();
		}
	}

	render(){
		return (<div id="ad-remove"><button type="button" className="btn btn-danger btn-sm" onClick={this.RemoveAd}>Remove</button>
			<p className={this.state.info_class}  id="cr-info-field" >{this.state.info_text}</p>
			</div>);
	}
}
export class ModIndividualRemovalAPIButton extends Component{
	constructor(props){
		super(props);
		this.state = {info_text:"", info_class:""};

		this.RemoveAd = this.RemoveAd.bind(this);
	}

	async RemoveAd(){
		const uri=this.props.ad_src;
		const url= this.props.url;
		const name = this.props.name;
		var response = await APICalls.callModRemoveIndividualAds(name,uri,url);
		if("message" in response){
			if("errors" in response){
				var reasons_arr = []
				for(var reason in response['errors']){
					reasons_arr.push(response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({
					info_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ),
					info_class:"text-danger" 
				});
			}
			else{
				this.setState({
					info_text:<span>Authorization Failed, Please Refresh<br/></span>,
					info_class:"text-danger" 
				});
			}
		}
		else if("warn" in response){
			this.setState({info_text:response['warn'], info_class:"text-warning bg-dark"});
		}
		else{
			this.setState({info_text:response['log'], info_class:"text-success"});			
			this.props.updateDetailsCallback();
			this.props.onClickCallBack();

		}
	}

	render(){
		return (<div id="ad-remove-soft"><button type="button" className="btn btn-danger btn-sm" onClick={this.RemoveAd}>Remove Selected</button>
			<p className={this.state.info_class}  id="mi-info-field" >{this.state.info_text}</p>
			</div>);
	}
}
export class ModCompleteRemovalAPIButton extends Component{
	constructor(props){
		super(props);
		this.state = {info_text:"", info_class:""};

		this.RemoveAd = this.RemoveAd.bind(this);
	}

	async RemoveAd(){
		const name = this.props.name;

		var response = await APICalls.callModRemoveAllUserAds(name);
		if("message" in response){
			if("errors" in response){
				var reasons_arr = []
				for(var reason in response['errors']){
					reasons_arr.push(response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({
					info_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ),
					info_class:"text-danger" 
				});
			}
			else{
				this.setState({
					info_text:<span>Authorization Failed, Please Refresh<br/></span>,
					info_class:"text-danger" 
				});
			}
		}
		else if("warn" in response){
			this.setState({info_text:response['warn'], info_class:"text-warning bg-dark"});
		}
		else{
			this.setState({info_text:response['log'], info_class:"text-success"});			this.props.updateDetailsCallback();
			this.props.onClickCallBack();

		}
	}

	render(){
		return (<div id="ad-remove-hard"><button type="button" className="btn btn-danger btn-sm" onClick={this.RemoveAd}>Remove All</button>
			<p className={this.state.info_class}  id="mc-info-field" >{this.state.info_text}</p>
			</div>);
	}
}
export class ModSoftBanAPIButton extends Component{
	constructor(props){
		super(props);
		this.state = {info_text:"", info_class:""};

		this.SoftBan = this.SoftBan.bind(this);
	}

	async SoftBan(){
		const name = this.props.name;
		var response = await APICalls.callModBanUser(name, 0);
		if("message" in response){
			if("errors" in response){
				var reasons_arr = []
				for(var reason in response['errors']){
					reasons_arr.push(response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({
					info_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ),
					info_class:"text-danger" 
				});
			}
			else{
				this.setState({
					info_text:<span>Authorization Failed, Please Refresh<br/></span>,
					info_class:"text-danger" 
				});
			}
		}
		else if("warn" in response){
			this.setState({info_text:response['warn'], info_class:"text-warning bg-dark"});
		}
		else{
			this.setState({info_text:response['log'], info_class:"text-success"});			this.props.updateDetailsCallback();
			this.props.onClickCallBack();

		}
	}

	render(){
		return (<div id="ban-soft"><button type="button" className="btn btn-info btn-sm" onClick={this.SoftBan}>Soft Ban</button>
			<p className={this.state.info_class}  id="sb-info-field" >{this.state.info_text}</p>
			</div>);
	}
}
export class ModHardBanAPIButton extends Component{
	constructor(props){
		super(props);
		this.state = {info_text:"", info_class:""};

		this.HardBan = this.HardBan.bind(this);
	}

	async HardBan(){
		const name = this.props.name;

		var response = await APICalls.callModBanUser(name, 1);
		if("message" in response){
			if("errors" in response){
				var reasons_arr = []
				for(var reason in response['errors']){
					reasons_arr.push(response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({
					info_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ),
					info_class:"text-danger" 
				});
			}
			else{
				this.setState({
					info_text:<span>Authorization Failed, Please Refresh<br/></span>,
					info_class:"text-danger" 
				});
			}
		}
		else if("warn" in response){
			this.setState({info_text:response['warn'], info_class:"text-warning bg-dark"});
		}
		else{
			this.setState({info_text:response['log'], info_class:"text-success"});			
			this.props.updateDetailsCallback();
			this.props.onClickCallBack();

		}
	}

	render(){
		return (<div id="ban-hard"><button type="button" className="btn btn-info btn-sm" onClick={this.HardBan}>Hard Ban</button>
			<p className={this.state.info_class}  id="hb-info-field" >{this.state.info_text}</p>
			</div>);
	}
}

