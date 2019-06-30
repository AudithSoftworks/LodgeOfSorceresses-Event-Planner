import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "header-scss" */
    '../../../sass/_header.scss'
    );

import { library } from '@fortawesome/fontawesome-svg-core/index';
import { faChevronDown, faGlobe, faHome, faSignInAlt, faSignOutAlt, faUsers, faCampfire } from '@fortawesome/pro-solid-svg-icons/index';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome/index';
import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Link, NavLink, withRouter } from 'react-router-dom';
import { authorizeAdmin, authorizeUser } from "../../helpers";
import { characters, user } from '../../vendor/data';

library.add(faChevronDown, faGlobe, faHome, faSignInAlt, faSignOutAlt, faUsers, faCampfire);

class Header extends Component {
    renderNavLinks = navLinks => {
        return navLinks.map((item, idx) => {
            let { className } = item.props;
            let newClassName = 'nav-item';
            if (className) {
                newClassName += ' ' + className;
            }

            return (
                <li key={idx} className={newClassName}>
                    {item}
                </li>
            );
        });
    };

    render = () => {
        const { me } = this.props;
        const navLinks = [];
        if (me) {
            navLinks.push(
                <NavLink exact to="/dashboard" activeClassName="active" title="Home">
                    <FontAwesomeIcon icon="home" size="lg" />
                    <span className="d-none d-sm-inline-block">Home</span>
                </NavLink>
            );
        } else {
            navLinks.push(
                <NavLink exact to="/" activeClassName="active" title="Login">
                    <FontAwesomeIcon icon="sign-in-alt" size="lg" />
                    <span className="d-none d-sm-inline-block">Login</span>
                </NavLink>
            );
        }
        if (authorizeUser(this.props)) {
            navLinks.push(
                <NavLink to="/@me/characters" activeClassName="active" title="My Characters">
                    <FontAwesomeIcon icon="users" size="lg" />
                    <span className="d-none d-sm-inline-block">My Characters</span>
                </NavLink>
            );
        }
        if (authorizeAdmin(this.props)) {
            navLinks.push(

            );
        }

        const navLinksRendered = this.renderNavLinks(navLinks);

        let email = me ? me.email : null;
        if (email) {
            const posOfAtSignInEmail = email.indexOf('@');
            email = email.slice(0, posOfAtSignInEmail + 1) + '...';
        }

        const officerLink = authorizeAdmin(this.props) ? (
            <li>
                <Link to="/admin" title="Officer Area">
                    <FontAwesomeIcon icon="campfire" size="2x" />
                    <span className="d-none d-sm-inline-block">Officer Area</span>
                </Link>
            </li>
        ) : null;
        const navbarFirstSection = me ? (
            <li className="chevron">
                <figure>
                    <img alt={email || 'The Soulless One'} src={me ? me.avatar : "/images/touch-icon-ipad.png"} />
                    <figcaption>{email || 'The Soulless One'}</figcaption>
                </figure>
                <FontAwesomeIcon icon="chevron-down" className="ml-2" />
                <ul className="member-bar-dropdown">
                    {officerLink}
                    <li><a href="/logout"><FontAwesomeIcon icon="sign-out-alt" size="2x" /> Sign Out</a></li>
                </ul>
            </li>
        ) : (
            <li className="chevron">
                <figure>
                    <img alt={email || 'The Soulless One'} src={me && me.avatar ? me.avatar : "/images/touch-icon-ipad.png"} />
                    <figcaption>Welcome, Soulless One!</figcaption>
                </figure>
            </li>
        );

        return (
            <header className="container">
                <h1 className="col-xs-24 col-md-18">Lodge of Sorceresses</h1>
                <ul className="member-bar d-none d-md-flex">
                    {navbarFirstSection}
                    <li>
                        <a href="https://lodgeofsorceresses.com" title="Forums">
                            <FontAwesomeIcon icon="globe" size="lg" />
                        </a>
                    </li>
                </ul>
                <nav className="col-md-24">
                    <ul className="nav-tabs">{navLinksRendered}</ul>
                </nav>
            </header>
        );
    };
}

Header.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    groups: PropTypes.object,
    navLinks: PropTypes.arrayOf(NavLink),
    myCharacters: characters,
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    groups: state.getIn(['groups']),
    myCharacters: state.getIn(['myCharacters']),
    notifications: state.getIn(['notifications']),
});

export default withRouter(connect(mapStateToProps)(Header));
