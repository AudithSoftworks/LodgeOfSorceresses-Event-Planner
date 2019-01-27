import React, { Component } from 'react';
import {NavLink} from "react-router-dom";
import PropTypes from 'prop-types';

class Header extends Component {
    renderNavLinks = (navLinks) => {
        return navLinks.map((item, idx) => <li key={idx} className="nav-item">{item}</li>);
    };

    render = () => {
        return (
            <header className="container">
                <h1 className="col-md-16">Lodge of Sorceresses</h1>
                <ul className="member-bar col-md-8">
                    <li>
                        <figure>
                            <img src=""/>
                        </figure>
                    </li>
                </ul>
                <nav className="col-md-24">
                    <ul className="nav nav-tabs">
                        {this.renderNavLinks(this.props.navLinks)}
                    </ul>
                </nav>
            </header>
        );
    };
}

Header.propTypes = {
    navLinks: PropTypes.arrayOf(NavLink)
};

export default Header;
