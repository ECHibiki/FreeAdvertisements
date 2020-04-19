import React, { Component } from 'react';
import {DataStore, APICalls} from '../../network/api';
import {Sort} from '../sorting/sort';
import {AllDetailsTable} from '../table/all-details-table';
import {Link} from "react-router-dom";
export class AllContainer extends Component{
	constructor(props){
		super(props);
		this.state = {AdArray:[], filter:'none', sorting:'none'};
		this.setAllDetails = this.setAllDetails.bind(this);
		this.filterOnChange = this.filterOnChange.bind(this);
		this.sortByOnChange = this.sortByOnChange.bind(this);
	}

	componentDidMount(){
		APICalls.callRetrieveAllAds(this.setAllDetails, 'AdArray');
	}

	setAllDetails(state_obj){
		this.setState(state_obj);
	}

  filterOnChange(response){
    this.setState({filter:response});
  }
  sortByOnChange(response){
    this.setState({sorting:response});
  }

	render(){
			return (
				<div id="all-container">
					<h2>All Banners</h2>
					<span className="all-link"><Link to="/">Back</Link></span><br/>

					<Sort sortChange={this.sortByOnChange} filterChange={this.filterOnChange} />

				  <AllDetailsTable adData={this.state.AdArray} updateDetailsCallback={this.setAllDetails}
            filterDetails={this.state.filter}
            sortingDetails={this.state.sorting}/>
			  </div>);
	}
}
