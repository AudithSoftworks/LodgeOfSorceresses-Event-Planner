import { library } from "@fortawesome/fontawesome-svg-core";
import { faHome, faUsers, faUsersCog } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { Component } from 'react';
import { NavLink } from "react-router-dom";
import Footer from "./Layout/Footer";
import Header from "./Layout/Header";
import Main from "./Layout/Main";

library.add(faHome, faUsers, faUsersCog);

class Application extends Component {
    navLinks = [
        <NavLink exact to="/" activeClassName="active" title='Dashboard'><FontAwesomeIcon icon="home" size='lg'/></NavLink>,
        <NavLink to="/chars" activeClassName="active" title='Characters'><FontAwesomeIcon icon="users" size='lg'/></NavLink>,
        <NavLink to="/admin" activeClassName="active" className='pull-right' title='Officer Panel'><FontAwesomeIcon icon="users-cog" size='lg'/></NavLink>,
    ];

    render = () => {
        return [
            <Header key="header" navLinks={this.navLinks}/>,
            <Main key="main"/>,
            <Footer key="footer"/>
        ];
    };
}

export default Application;
