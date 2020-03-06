import React, { Component } from 'react';
import {DataStore, APICalls} from '../network/api';
import Popup from "reactjs-popup";
import {HelperText, TopHeader, AdDetailsTable, AllDetailsTable, ModDetailsTable} from "./information-components";
import {SignInForm, SignInButton, CreationForm, CreateButton, AdCreationForm, AdCreateButton, AdRemovalButton, AdRemovalForm } from "./form-components"
import {SampleBanner, PatreonBanner, LoadingSpinner} from "./image-components";

import {
  BrowserRouter as Router,
  Switch,
  Route,
  Link,
  useRouteMatch,
  useParams
} from "react-router-dom";



export class MasterPage extends Component{
	constructor(props){
		super(props);
		this.state = {auth: undefined};
		this.swapPage = this.swapPage.bind(this);
	}

	componentDidMount(){
		this.checkLoggedIn();
	}

	async checkLoggedIn(){
		var instant_login = await APICalls.callRetrieveUserAds();
		if("message" in instant_login){
			this.setState({auth: false});
		}
		else{
			this.setState({auth: true});
		}
	}
	swapPage(){
		this.setState({auth: !this.state.auth});
	}

	render(){
		if(this.state.auth == undefined){
			return(<div id="master-waiting">
				<div id="upper-master-login">
				 <TopHeader />
				 <SampleBanner />
				</div>
				<div id="lower-master-waiting">
				 <hr/>
				 <LoadingSpinner />
				</div>
			       </div>);
		}
		else if(!this.state.auth){
			return(<div id="master-login">
				<div id="upper-master-login">
				  <TopHeader />
				  <SampleBanner />
				</div>
				<hr/>
				  <div id="mid-master-login">
				    <LoginContainer swapPage={this.swapPage}/>
				   </div>
				  <hr/>
				   <div id="lower-master-login">
					<PatreonBanner />
					<HelperText />
				   </div>
				</div>);
		}
		else{
			return(<div id="master-user">
				<div id="upper-master-user">
				<TopHeader />
				<SampleBanner />
				</div>
				<div id="mid-master-user">
				  <UserContainer/>
				</div>
				<div id="lower-master-user">
				  <PatreonBanner />
				  <HelperText />
				</div>
				</div>);
		}
	}
}
export class LoginContainer extends Component{
	constructor(props){ 
		super(props);
		this.state = {si_visibility:"unset", si_height:"0em", si_opacity:"0", c_visibility:"unset", c_height:"0em", c_opacity:"0"};

		this.SignInOnClick = this.SignInOnClick.bind(this);
		this.CreateOnClick = this.CreateOnClick.bind(this);
		
	}

	SignInOnClick(){
		if(this.state.si_visibility=="unset"){
			this.setState({si_visibility:"initial", si_height:"17em", si_opacity:"1"});
		}
		else{
			this.setState({si_visibility:"unset", si_height:"0em", si_opacity:"0"});
		}
	}

	CreateOnClick(){
		if(this.state.c_visibility=="unset"){
			this.setState({c_visibility:"initial", c_height:"22.5em", c_opacity:"1"});
		}
		else{
			this.setState({c_visibility:"unset", c_height:"0em", c_opacity:"0"});
		}
	}

	render(){
		    return (<div id="login-container">
			    <div className="mid-header-container">
			    <h2>Authentication</h2>
			    </div>
			    <div id="si-button-container">
			    <SignInButton onClickCallBack={this.SignInOnClick}/>
				<SignInForm  swapPage={this.props.swapPage} opacity={this.state.si_opacity} visibility={this.state.si_visibility} height={this.state.si_height} />
				<CreateButton onClickCallBack={this.CreateOnClick}/>
				<CreationForm swapPage={this.props.swapPage} opacity={this.state.c_opacity} visibility={this.state.c_visibility} height={this.state.c_height} />
			    </div>
			    <span className="all-span"><Link to="/all">View All</Link></span>

			</div>)
	}
}
export class UserContainer extends Component{
	constructor(props){
		super(props);
		this.AdCreateOnClick = this.AdCreateOnClick.bind(this);
		this.state = {AdCVisibility:"unset", AdCHeight:"0em", AdCOpacity:"0", AdArray:[], mod:false};
		this.UpdateDetails = this.UpdateDetails.bind(this);
	}

	componentDidMount(){
		this.UpdateDetails();
	}

	AdCreateOnClick(){
		if(this.state.AdCVisibility == "unset")
			this.setState({AdCVisibility:"initial", AdCHeight:"16.4em", AdCOpacity:"1"});
		else
			this.setState({AdCVisibility:"unset", AdCHeight:"0em", AdCOpacity:"0"});
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
			this.setState({AdArray:d_response['ads'], mod:d_response['mod']});	
		}

	}

	render(){
		if(this.state.mod){
			var mod_button = (<div className="mod-link"><Link to="/mod">Mod Mode</Link></div>);
		}
		return (<div id="user-container">
				<h2>Your Banners</h2>
				<div id="ad-button-container">
				  <AdCreateButton onClickCallBack={this.AdCreateOnClick}/>
				  <AdCreationForm visibility={this.state.AdCVisibility} opacity={this.state.AdCOpacity} height={this.state.AdCHeight} UpdateDetails={this.UpdateDetails}/>
				</div>
				<AdDetailsTable adData={this.state.AdArray} updateDetailsCallback={this.UpdateDetails}/>
				{mod_button}
				<div className="all-link"><Link to="/all">View All</Link></div>
			</div>)
	}

}


export class AllPage extends Component{
	render(){
			return(<div id="master-all">
				<div id="upper-master-all">
				  <TopHeader />
				  <SampleBanner />
				</div>
				<hr/>
				  <div id="mid-master-all">
				    <AllContainer />
				   </div>
				  <hr/>
				   <div id="lower-master-all">
					<PatreonBanner />
					<HelperText />
				   </div>
				</div>);
	}
}
export class AllContainer extends Component{
	constructor(props){
		super(props);
		this.state = {AdArray:[]}
		this.setAllDetails = this.setAllDetails.bind(this);
	}

	componentDidMount(){
		APICalls.callRetrieveAllAds(this.setAllDetails, 'AdArray');
	}

	setAllDetails(state_obj){
		this.setState(state_obj);
	}

	render(){
			return (<div id="all-container">
				<h2>All Banners</h2>
				  <AllDetailsTable adData={this.state.AdArray} updateDetailsCallback={this.setAllDetails}/>
				<span className="all-link"><Link to="/">Back</Link></span>
			</div>);
	}
}


export class ModPage extends Component{
	render(){
			return(<div id="master-mod">
				<div id="upper-master-mod">
				  <TopHeader />
				  <SampleBanner />
				</div>
				<hr/>
				  <div id="mid-master-mod">
				    <ModContainer />
				   </div>
				  <hr/>
				   <div id="lower-master-mod">
					<PatreonBanner />
					<HelperText />
				   </div>
				</div>);
	}
}
export class ModContainer extends Component{
	constructor(props){
		super(props);
		this.state = {AdArray:[]}
		this.setAllDetails = this.setAllDetails.bind(this);
		this.UpdateDetails = this.UpdateDetails.bind(this);

	}

	componentDidMount(){
		APICalls.callRetrieveModAds(this.setAllDetails, 'AdArray');
	}
	// set warnings from afar
	setAllDetails(state_obj){
		this.setState(state_obj);
	}
	async UpdateDetails(){
		APICalls.callRetrieveModAds(this.setAllDetails, 'AdArray');	
	}

	render(){	
		return (<div id="mod-container">
			<h2>All Banners</h2>
			  <ModDetailsTable adData={this.state.AdArray} updateDetailsCallback={this.UpdateDetails}/>
			<span className="mod-link"><Link to="/">Back</Link></span>
		</div>);
	}
}
