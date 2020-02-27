import React, { Component } from 'react';

export class MasterPage extends Component{
	render(){
		return(<div id="master"></div>)
	}
}


export class TopHeader extends Component{
	render(){
		return (<div id="top-head"><h1>Custom<br/>&emsp;Banners</h1></div>);
	}
}
export class SignInButton extends Component{
	render(){
		return (<div id="sign-in-start"><button type="button" className="btn btn-primary" >Sign In</button></div>);
	}

}
export class CreateButton extends Component{
	render(){
		return (<div id="create-start"><button type="button" class="btn btn-outline-dark" >Sign In</button></div>);
	}

}
export class PatreonBanner extends Component{
	render(){
		return (<div id="patreon"><a href=""><img src="https://abdullahsameer-8c5e.kxcdn.com/blog/wp-content/uploads/2018/11/support-my-work-on-patreon-banner-image-600px.png"/></a></div>);
	}

}
export class SampleBanner extends Component{
	render(){
		return (<div id="sample-banner"><iframe src="/banner" scroll="no" width="300" height="100"></iframe></div>);
	}
}
export class SignInForm extends Component{
	constructor(props){
		super(props);
		this.state = {visibility: "hidden", height: "10em"};
	}
	render(){
		return(<div style={{visibility: this.state.visibility, height: this.state.height}} id="sign-form">
			<form>
			
				<div className="form-group">
					<label htmlFor="name-si">UserName</label>
					<input className="form-control" id="name-si" placeholder="insert username" required/>
				</div>
				<div className="form-group">
					<label htmlFor="pass-si">Password</label>
					<input type="password" className="form-control" id="pass-si" placeholder="" required/>
				</div>
				<SignInAPIButton />
			</form></div>);
	}
}
export class CreationForm extends Component{
	constructor(props){
		super(props);
		this.state = {visibility: "hidden", height: "10em"};
	}
	render(){
		return(<div style={{visibility: this.state.visibility, height: this.state.height}} id="create-form">
			<form>
				<div className="form-group">
					<label htmlFor="name-c">UserName</label>
					<input className="form-control" id="name-c" placeholder="insert username" required/>
				</div>
				<div className="form-group">
					<label htmlFor="pass-c">Password</label>
					<input type="password" className="form-control" id="pass-c" placeholder="" required/>
				</div>
				<div className="form-group">
					<label htmlFor="pass-c-conf">Password</label>
					<input type="password" className="form-control" id="pass-c-conf" placeholder="" required/>
				</div>

				<CreateAPIButton />
			</form></div>);
	}

}
export class AdCreationForm extends Component{
	constructor(props){
		super(props);
		this.state = {display: "none", height: "10em"};
	}
	render(){
		return(<div style={{display: this.state.display, height: this.state.height}} id="ad-create-form">
			<form>
				<div className="form-group">
					<label htmlFor="image-ad-c">Image</label>
					<input type="file" className="form-control-file" id="image-ad-c" required/>
					<small className="form-text text-muted">Must be 300x100</small>
				</div>
				<div className="form-group">
					<label htmlFor="ad-url-c">URL</label>
					<input type="url" pattern="https://.*" className="formui-control" id="ad-url-c" placeholder="https://.*" required/>
				</div>

				<AdCreateButton />
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
				<AdRemovalButton />
			</form></div>);
	}

}
export class SignInAPIButton extends Component{
	render(){
		return (<div id="sign-in-finish"><button type="button" className="btn btn-outline-secondary">Sign In</button></div>);
	}

}
export class CreateAPIButton extends Component{
	render(){
		return (<div id="create-finish"><button type="button" className="btn btn-outline-secondary">Create</button></div>);
	}

}
export class AdCreateButton extends Component{
	render(){
		return (<div id="ad-create"><button type="button" className="btn btn-outline-primary">New Banner</button></div>);
	}

}
export class AdRemovalButton extends Component{
	render(){
		return (<div id="ad-remove"><button type="button" className="btn btn-danger btn-sm">Remove</button></div>);
	}

}
export class AdDetailsTable extends Component{
	render(){
		return (<div id="details-table" className="table table-striped table-responsive">
			<table className="">
				<caption><SampleBanner/></caption>		
				<thead className="thead-dark">
					<tr>
						<th>Remove</th>
						<th>Image</th>
						<th>URL</th>
					</tr>
				</thead>
				<tbody className="">
				
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
				<td><AdRemovalButton/></td>
				<td><img src={this.props.ad_src}/></td>
				<td><a href={this.props.url}>{this.props.url}</a></td>
			</tr>);
	}
}

