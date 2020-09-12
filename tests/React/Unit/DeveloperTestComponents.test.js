import { TopHeader } from '../../../resources/js/components/components';
import { SignInButton } from '../../../resources/js/components/components';
import { CreateButton } from '../../../resources/js/components/components';
import { PatreonBanner } from '../../../resources/js/components/components';
import { SampleBanner } from '../../../resources/js/components/components';

import { SignInForm } from '../../../resources/js/components/components';
import { CreationForm } from '../../../resources/js/components/components';
import { AdCreationForm } from '../../../resources/js/components/components';
import { AdRemovalForm } from '../../../resources/js/components/components';

import { AdCreateButton } from '../../../resources/js/components/components';
import { AdRemovalButton } from '../../../resources/js/components/components';
import { SignInAPIButton } from '../../../resources/js/components/components';
import { CreateAPIButton } from '../../../resources/js/components/components';

import { AdDetailsTable } from '../../../resources/js/components/components';
import { AdDetailsEntry } from '../../../resources/js/components/components';

import React from 'react';
import {render} from '@testing-library/react';
import Adapter from 'enzyme-adapter-react-16';
import { shallow, configure } from 'enzyme';

configure({adapter: new Adapter()});

describe("Is Static Component",function(){
		test("Static Header", function(){
			const top_head = shallow(<TopHeader />);
			expect(top_head.html()).toMatchSnapshot();
		});

		test("Static patreon", function(){
			const patreon = shallow(<PatreonBanner/>);
			expect(patreon.html()).toMatchSnapshot();
		})	
		test("Static banner", function(){
			const banner = shallow(<SampleBanner/>);
			expect(banner.html()).toMatchSnapshot();
		});
	}
);
describe("Is Button Component",function(){
		test("Button Signin", function(){
			const si_button = shallow(<SignInButton/>);
			expect(si_button.html()).toMatchSnapshot();
		});
		test("Button create", function(){
			const c_button = shallow(<CreateButton/>);
			expect(c_button).toMatchSnapshot()
		});
		test("Button signinUser", function(){
			const siu_button = shallow(<SignInAPIButton/>);
			expect(siu_button.html()).toMatchSnapshot();

		});
		test("Button createUser", function(){
			const cu_button = shallow(<CreateAPIButton/>);
			expect(cu_button.html()).toMatchSnapshot();

		});
		test("Button createAd", function(){
			const ca_button = shallow(<AdCreateButton/>);
			expect(ca_button.html()).toMatchSnapshot();

		});
		test("Button DeleteAd", function(){
			const d_button = shallow(<AdRemovalButton/>);
			expect(d_button.html()).toMatchSnapshot();		
		});
	}
);
describe("Is Form Component",function(){
		test("Form Signin", function(){
			const si_form = shallow(<SignInForm/>);
			expect(si_form.html()).toMatchSnapshot();		
		});
		test("Form create", function(){
			const c_form = shallow(<CreationForm/>);
			expect(c_form.html()).toMatchSnapshot();		
		});
		test("Form ad create", function(){
			const c_form = shallow(<AdCreationForm/>);
			expect(c_form.html()).toMatchSnapshot();		
		});
		test("Form ad remove", function(){
			const r_form = shallow(<AdRemovalForm/>);
			expect(r_form.html()).toMatchSnapshot();		
		});

	}
);
describe("Is Table Component",function(){
		test("Empty Table", function(){
			const table = shallow(<AdDetailsTable/>);
			expect(table.html()).toMatchSnapshot();		
		});
		test("Ad Entry", function(){
			const row = shallow(<AdDetailsEntry ad_src="" url="" id=""/>);
			expect(row.html()).toMatchSnapshot();
		});
	}
);

