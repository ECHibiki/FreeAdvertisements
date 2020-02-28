import React, { Component } from 'react';
import {DataStore, APICalls} from './api';
import Popup from "reactjs-popup";

export class MasterPage extends Component{
	constructor(props){
		super(props);
		this.state = {auth: undefined};
		this.swapPage = this.swapPage.bind(this);
		this.checkLoggedIn();
	}

	async checkLoggedIn(){
		console.log(DataStore.getAuthToken());
		var instant_login = await APICalls.callRetrieveUserAds();
		console.log(instant_login);
		if("message" in instant_login){
			this.setState({auth: false});
		}
		else{
			this.setState({auth: true});
		}
	}

	swapPage(ruin){
		this.setState({auth: !this.state.auth});
	}

	render(){
		console.log(this.state.auth);
		if(this.state.auth == undefined){
			return(<div id="master-waiting"><TopHeader />
			  <div id="lower-master-waiting">
				<PatreonBanner />
				<hr/>
				<img src="/Spinner-1s-200px.gif" />
			  </div>
			</div>);
		}
		else if(!this.state.auth){
			return(<div id="master-login"><TopHeader />
				  <div id="lower-master-login">
					<PatreonBanner />
					<LoginContainer swapPage={this.swapPage}/>
					<HelperText />
				  </div>
				</div>);
		}
		else{
			return(<div id="master-user"><TopHeader />
				<div id="lower-master-user"><PatreonBanner />
				<UserContainer />
				<HelperText />
				</div>
				</div>);
		}
	}
}
export class LoginContainer extends Component{
	constructor(props){
		super(props);
		this.state = {si_display:"none", si_height:"0em", c_display:"none", c_height:"0em"};

		this.SignInOnClick = this.SignInOnClick.bind(this);
		this.CreateOnClick = this.CreateOnClick.bind(this);
		
	}

	SignInOnClick(){
		if(this.state.si_display=="none"){
			this.setState({si_display:"unset", si_height:"15em"});
		}
		else{
			this.setState({si_display:"none", si_height:"0em"});
		}
	}

	CreateOnClick(){
		if(this.state.c_display=="none"){
			this.setState({c_display:"unset", c_height:"15em"});
		}
		else{
			this.setState({c_display:"none", c_height:"0em"});
		}
	}

	render(){
		    return (<div id="login-container">
				<h2>Authentication</h2>
				<SignInButton onClickCallBack={this.SignInOnClick}/>
				<SignInForm  swapPage={this.props.swapPage} display={this.state.si_display} height={this.state.si_height} />
				<CreateButton onClickCallBack={this.CreateOnClick}/>
				<CreationForm swapPage={this.props.swapPage}  display={this.state.c_display} height={this.state.c_height} />
			</div>)
	}
}
export class UserContainer extends Component{
	constructor(props){
		super(props);
		this.AdCreateOnClick = this.AdCreateOnClick.bind(this);
		this.state = {AdCDisplay:"none", AdCHeight:"0em", AdArray:[]};
		this.UpdateDetails = this.UpdateDetails.bind(this);
	}

	componentDidMount(){
		this.UpdateDetails();
	}

	AdCreateOnClick(){
		if(this.state.AdCDisplay == "none")
			this.setState({AdCDisplay:"unset", AdCHeight:"10em"});
		else
			this.setState({AdCDisplay:"none", AdCHeight:"0em"});
	}

	async UpdateDetails(){
		var d_response = await APICalls.callRetrieveUserAds();	
		if("message" in d_response){
			if("errors" in d_response){
				var reasons_arr = []
				for(var reason in d_response['errors']){
					reasons_arr.push(d_response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({err_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ), war_text: "", suc_text:""});
			}
			else{
				this.setState({err_text:<span>Authorization Failed, Please Refresh<br/></span>});
			}
		}
		else if("warn" in d_response){
			this.setState({war_text:d_response['warn']});
		}
		else{
			console.log(d_response['ads']);
			this.setState({AdArray:d_response['ads']});	
		}

	}

	render(){
		console.log(this.state.AdArray);
		return (<div id="user-container">
				<h2>Your Banners</h2>
				<AdCreateButton onClickCallBack={this.AdCreateOnClick}/>
				<AdCreationForm display={this.state.AdCDisplay} height={this.state.AdCHeight} UpdateDetails={this.UpdateDetails}/>
				<AdDetailsTable adData={this.state.AdArray} updateDetailsCallback={this.UpdateDetails}/>
			</div>)
	}

}

export class HelperText extends Component{
	render(){
		return (<div id="helper">
		<h2>How To Use</h2>
		<SampleBanner />
		<p>To embed on a website use <span className="code">{'<iframe src="/banner" scrolling="no" width="300" height="100"></iframe>'}</span>. 
			Uploaded images must be 300x100 and safe for work</p>
		</div>)
	};
}
export class TopHeader extends Component{
	render(){
		return (<div id="top-head"><h1>Custom<br/>&emsp;Banners</h1></div>);
	}
}
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
		return (<div id="create-ad-start"><button onClick={this.props.onClickCallBack} type="button" className="btn btn-outline-primary" >New Banner</button></div>);
	}

}
export class PatreonBanner extends Component{
	render(){
		return (<div id="patreon"><a href="https://patreon.com/ECVerniy"><img src="https://abdullahsameer-8c5e.kxcdn.com/blog/wp-content/uploads/2018/11/support-my-work-on-patreon-banner-image-600px.png"/></a></div>);
	}

}
export class SampleBanner extends Component{
	render(){
		return (<div id="sample-banner"><iframe src="/banner" scrolling="no" width="300" height="100"></iframe></div>);
	}
}
export class SignInForm extends Component{
	constructor(props){
		super(props);
	}
	render(){
		return(<div style={{display: this.props.display, height: this.props.height}} id="sign-form">
			<form>
			
				<div className="form-group">
					<label htmlFor="name-si">UserName</label>
					<input className="form-control" id="name-si" placeholder="insert username" required/>
				</div>
				<div className="form-group">
					<label htmlFor="pass-si">Password</label>
					<input type="password" className="form-control" id="pass-si" placeholder="" required/>
				</div>
				<SignInAPIButton swapPage={this.props.swapPage}  />
			</form></div>);
	}
}
export class CreationForm extends Component{
	constructor(props){
		super(props);
	}
	render(){
		return(<div style={{display: this.props.display, height: this.props.height}} id="create-form">
			<form>
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
			</form></div>);
	}

}
export class AdCreationForm extends Component{
	constructor(props){
		super(props);
	}
	render(){
		return(<div style={{display: this.props.display, height: this.props.height}} id="ad-create-form">
			<form>
				<div className="form-group">
					<label htmlFor="image-ad-c">Image</label>
					<input type="file" className="form-control-file" id="image-ad-c" accept="image/*" required/>
					<small className="form-text text-muted">Must be 300x100 and SFW</small>
				</div>
				<div className="form-group">
					<label htmlFor="ad-url-c">URL</label>
					<input type="url" pattern="https://.*" className="formui-control" id="ad-url-c" placeholder="https://.*" required/>
				</div>

				<AdCreateAPIButton UpdateDetails={this.props.UpdateDetails}/>
			</form></div>);
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
		console.log(si_response);
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


	render(){
		return (<div id="sign-in-finish"><button type="button" className="btn btn-outline-secondary" onClick={this.SendUserSignIn}>Sign In</button>
			<br/><p className="text-danger" id="c-error-field">{this.state.err_text}</p>
			<p className="text-warning" id="c-war-field">{this.state.war_text}</p>
			<p className="text-success" id="c-win-field">{this.state.suc_text}</p>
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
		console.log(name, pass, pass_confirmation);
		var response = await APICalls.callCreate(name, pass, pass_confirmation);
		console.log(response);
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
			console.log(si_response);
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

	render(){
		return (<div id="create-finish"><button type="button" className="btn btn-outline-secondary" onClick={this.SendUserCreate}>Create</button>
			<br/><p className="text-danger" id="c-error-field">{this.state.err_text}</p>
			<p className="text-warning" id="c-war-field">{this.state.war_text}</p>
			<p className="text-success" id="c-win-field">{this.state.suc_text}</p>
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
		console.log("--snb--");
		var image = document.getElementById("image-ad-c").files[0];		
		var url = document.getElementById("ad-url-c").value;
		console.log(image);
		console.log(url);
		var nb_response = await APICalls.callCreateNewAd(image, url);
		console.log(nb_response);
		if("message" in nb_response){
			if("errors" in nb_response){
				var reasons_arr = []
				for(var reason in nb_response['errors']){
					reasons_arr.push(nb_response['errors'][reason]);
				}
				var key_ind = 0;
				this.setState({err_text:reasons_arr.map((r) => <span key={key_ind++}>{r}<br/></span> ), war_text: "", suc_text:""});
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
		return (<div id="ad-create-finish"><button type="button" className="btn btn-outline-danger" onClick={this.SendNewBanner}>Create</button>
			<br/><p className="text-danger" id="c-error-field">{this.state.err_text}</p>
			<p className="text-warning" id="c-war-field">{this.state.war_text}</p>
			<p className="text-success" id="c-win-field">{this.state.suc_text}</p>
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
		console.log(rem_response);
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
			console.log(this.props.updateDetailsCallback)
			this.props.updateDetailsCallback();
			this.props.onClickCallBack();

		}
	}

	render(){
		return (<div id="ad-remove"><button type="button" className="btn btn-danger btn-sm" onClick={this.RemoveAd}>Remove</button>
			<br/><p className="text-danger" id="c-error-field">{this.state.err_text}</p>
			<p className="text-warning" id="c-war-field">{this.state.war_text}</p>
			<p className="text-success" id="c-win-field">{this.state.suc_text}</p>
			</div>);
	}
}
export class AdDetailsTable extends Component{
	constructor(props){
		super(props);
		this.state = {row_data:[]}
	}
	
	JSXRowData(adData){
		var JSX_var = [];
		console.log(adData)
		for(var index in adData){
			var entry = adData[index];
			entry['uri'] = entry['uri'].replace('public/image/', 'storage/image/');
			JSX_var.push(<AdDetailsEntry updateDetailsCallback={this.props.updateDetailsCallback} id={"banner-" + index} key={"banner-"+index} ad_src={entry['uri']} url={entry['url']}/>);
		}
		console.log(JSX_var);
		return JSX_var;
	}

	render(){
		return (<div id="details-table" className="table table-striped table-responsive">
			<table className="">
				<caption>ありがとうございます!</caption>		
				<thead className="thead-dark">
					<tr>
						<th>Remove</th>
						<th>Image</th>
						<th>URL</th>
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
				<td><AdRemovalButton updateDetailsCallback={this.props.updateDetailsCallback}  ad_src={this.props.ad_src} url={this.props.url}/></td>
				<td><a href={this.props.ad_src}><img src={this.props.ad_src}/></a></td>
				<td><a href={this.props.url}>{this.props.url}</a></td>
			</tr>);
	}
}

