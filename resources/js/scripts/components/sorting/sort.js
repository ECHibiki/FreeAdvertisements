import React, { Component } from 'react';
export class Sort extends Component{
	constructor(props){
		super(props);

		this.filterOnChange = this.filterOnChange.bind(this);
		this.sortByOnChange = this.sortByOnChange.bind(this);
		this.filter = this.getLocalStoragePresets()['filter'] == undefined ? 'none' : this.getLocalStoragePresets()['filter'];
		this.sort = this.getLocalStoragePresets()['sort'] == undefined ? 'none' : this.getLocalStoragePresets()['sort'];
	}

	filterOnChange(response){
		this.props.filterChange(response.target.value);
		this.setFilterPresets(response.target.value);
	}
	sortByOnChange(response){
		this.props.sortChange(response.target.value);
		this.setSortPresets(response.target.value);
	}

	getLocalStoragePresets(){
		return {
			filter: localStorage.getItem('filter'),
			sort: localStorage.getItem('sort')
		}
	}
	setFilterPresets(filter, sort){
			localStorage.setItem('filter', filter);
	}
	setSortPresets(sort){
			localStorage.setItem('sort', sort);
	}
	render(){
		return (<div id="select-fields">
		          <span id="filter-selections">Banner Size:&nbsp;
		            <select onChange={this.filterOnChange} defaultValue={this.filter}>
		              <option value='none'>All</option>
		              <option value='small'>Small</option>
		              <option value='wide'>Wide</option>
		            </select>
		          </span>
		          <span id="sortby-selections"> Sort By:&nbsp;
		            <select onChange={this.sortByOnChange} defaultValue={this.sort}>
		              <option value='none'>Chronological</option>
		              <option value='asc'>Ascending</option>
		              <option value='desc'>Descending</option>
		            </select>
		          </span>
		        </div>);
	}
}
