import React, { Component } from 'react';
import { NavLink } from "react-router-dom";
import Header from "./Layout/Header";
import Main from "./Layout/Main";
import Footer from "./Layout/Footer";

class Application extends Component {
    navLinks = [
        <NavLink exact to="/" activeClassName="active">Dashboard</NavLink>,
        <NavLink to="/chars" activeClassName="active">Characters</NavLink>,
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
