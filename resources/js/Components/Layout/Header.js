import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "header-scss" */
    '../../../sass/_header.scss'
);

import { faCalendarAlt, faCampfire, faChess, faChevronDown, faGlobe, faHome, faSignInAlt, faSignOutAlt, faUsers, faUsersClass } from '@fortawesome/pro-light-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome/index';
import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Link, NavLink, withRouter } from 'react-router-dom';
import { authorizeAdmin, authorizeUser } from '../../helpers';
import { characters, user } from '../../vendor/data';

class Header extends Component {
    constructor(props) {
        super(props);
        this.authorizeAdmin = authorizeAdmin.bind(this);
        this.authorizeUser = authorizeUser.bind(this);
    }

    renderNavLinks = navLinks => {
        return navLinks.map((item, idx) => {
            const { className } = item.props;
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
        const memberBarDropdownLinks = [];
        if (me) {
            navLinks.push(
                <NavLink exact to="/dashboard" activeClassName="active" title="Home">
                    <FontAwesomeIcon icon={faHome} size="lg" />
                    <span className="d-none d-sm-inline-block">Home</span>
                </NavLink>
            );
        } else {
            navLinks.push(
                <NavLink exact to="/" activeClassName="active" title="Login">
                    <FontAwesomeIcon icon={faSignInAlt} size="lg" />
                    <span className="d-none d-sm-inline-block">Login</span>
                </NavLink>
            );
        }
        if (this.authorizeUser(true)) {
            navLinks.push(
                <NavLink to="/events" activeClassName="active" title="Calendar">
                    <FontAwesomeIcon icon={faCalendarAlt} size="lg" />
                    <span className="d-none d-sm-inline-block">Calendar</span>
                </NavLink>
            );
            navLinks.push(
                <NavLink to="/users" activeClassName="active" title="Roster">
                    <FontAwesomeIcon icon={faUsers} size="lg" />
                    <span className="d-none d-sm-inline-block">Roster</span>
                </NavLink>
            );
            navLinks.push(
                <NavLink to="/teams" activeClassName="active" title="Teams">
                    <FontAwesomeIcon icon={faUsersClass} size="lg" />
                    <span className="d-none d-sm-inline-block">Teams</span>
                </NavLink>
            );

            memberBarDropdownLinks.push(
                <li key='my_characters'><Link to="/@me/characters" title="My Characters"><FontAwesomeIcon icon={faChess} size="2x" fixedWidth /> My Characters</Link></li>
            );
        }

        const navLinksRendered = this.renderNavLinks(navLinks);

        let email = me ? me.email : null;
        if (email) {
            const posOfAtSignInEmail = email.indexOf('@');
            email = email.slice(0, posOfAtSignInEmail + 1) + '...';
        }

        if (this.authorizeAdmin()) {
            memberBarDropdownLinks.push(
                <li key='officer_area'>
                    <Link to="/admin" title="Officer Area">
                        <FontAwesomeIcon icon={faCampfire} size="2x" fixedWidth />
                        <span className="d-none d-sm-inline-block">Officer Area</span>
                    </Link>
                </li>
            );
        }
        memberBarDropdownLinks.push(
            <li key='sign_out'><a href="/logout"><FontAwesomeIcon icon={faSignOutAlt} size="2x" fixedWidth /> Sign Out</a></li>
        );

        const memberBarFirstSection = me ? (
            <li className="chevron" aria-haspopup='true'>
                <figure>
                    <img alt={email || 'The Soulless One'} src={me && me.avatar ? me.avatar : '/images/touch-icon-ipad.png'} />
                    <figcaption>{email || 'The Soulless One'}</figcaption>
                </figure>
                <FontAwesomeIcon icon={faChevronDown} className="ml-2" />
                <ul className="member-bar-dropdown">
                    {memberBarDropdownLinks}
                </ul>
            </li>
        ) : (
            <li className="chevron">
                <figure>
                    <img alt={email || 'The Soulless One'} src={me && me.avatar ? me.avatar : '/images/touch-icon-ipad.png'} />
                    <figcaption>Welcome, Soulless One!</figcaption>
                </figure>
            </li>
        );

        return (
            <header className="container">
                <h1 className="col-xs-24 col-md-18">Lodge of Sorceresses</h1>
                <ul className="member-bar d-none d-md-flex">
                    {memberBarFirstSection}
                    <li>
                        <a href="https://lodgeofsorceresses.com" title="Forums">
                            <FontAwesomeIcon icon={faGlobe} size="lg" />
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
