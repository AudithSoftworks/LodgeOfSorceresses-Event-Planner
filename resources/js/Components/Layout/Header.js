import(/* webpackPrefetch: true, webpackChunkName: "header-scss" */ '../../../sass/_header.scss');

import { faAnalytics, faCampfire, faChess, faChevronDown, faGlobe, faHome, faHomeHeart, faSignInAlt, faSignOutAlt, faUsers, faUsersClass } from '@fortawesome/pro-light-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Link, NavLink, withRouter } from 'react-router-dom';
import { authorizeUser } from '../../helpers';
import { characters, user } from '../../vendor/data';

class Header extends Component {
    constructor(props) {
        super(props);
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
            const canAccessDashboard = me.isMember || me.isSoulshriven;
            navLinks.push(
                <NavLink exact to={canAccessDashboard ? '/@me' : '/home'} activeClassName="active" title={canAccessDashboard ? 'Dashboard' : 'Home'}>
                    <FontAwesomeIcon icon={canAccessDashboard ? faHomeHeart : faHome} size="lg" />
                    <span className="d-none d-sm-inline-block">{canAccessDashboard ? 'Dashboard' : 'Home'}</span>
                </NavLink>,
            );
        } else {
            navLinks.push(
                <NavLink exact to="/" activeClassName="active" title="Login">
                    <FontAwesomeIcon icon={faSignInAlt} size="lg" />
                    <span className="d-none d-sm-inline-block">Login</span>
                </NavLink>,
            );
        }
        if (this.authorizeUser(true) && (me.isMember ? me.linkedAccountsParsed.ips : true)) {
            // navLinks.push(
            //     <NavLink to="/events" activeClassName="active" title="Calendar">
            //         <FontAwesomeIcon icon={faCalendarAlt} size="lg" />
            //         <span className="d-none d-sm-inline-block">Calendar</span>
            //     </NavLink>,
            // );
            navLinks.push(
                <NavLink to="/users" activeClassName="active" title="Roster">
                    <FontAwesomeIcon icon={faUsers} size="lg" />
                    <span className="d-none d-sm-inline-block">Roster</span>
                </NavLink>,
            );
            navLinks.push(
                <NavLink to="/teams" activeClassName="active" title="Teams">
                    <FontAwesomeIcon icon={faUsersClass} size="lg" />
                    <span className="d-none d-sm-inline-block">Teams</span>
                </NavLink>,
            );

            memberBarDropdownLinks.push(
                <li key="my_characters">
                    <Link to="/@me/characters" title="My Characters">
                        <FontAwesomeIcon icon={faChess} size="2x" fixedWidth /> My Characters
                    </Link>
                </li>,
            );
        }

        const navLinksRendered = this.renderNavLinks(navLinks);

        let email = me && me.email ? me.email : null;
        if (email) {
            const posOfAtSignInEmail = email.indexOf('@');
            if (posOfAtSignInEmail !== -1) {
                email = email.slice(0, posOfAtSignInEmail + 1) + '...';
            }
        }

        if (me && me.isAdmin) {
            memberBarDropdownLinks.push(
                <li key="officer_area">
                    <Link to="/admin" title="Officer Area">
                        <FontAwesomeIcon icon={faCampfire} size="2x" fixedWidth />
                        <span className="d-none d-sm-inline-block">Officer Area</span>
                    </Link>
                </li>,
            );
        }
        memberBarDropdownLinks.push(
            <li key="sign_out">
                <a href="/logout">
                    <FontAwesomeIcon icon={faSignOutAlt} size="2x" fixedWidth /> Logout
                </a>
            </li>,
        );

        const memberBarFirstSection = me ? (
            <li className="chevron" aria-haspopup="true">
                <figure>
                    <img alt={(me.name ? '@' + me.name : email) || 'The Soulless One'} src={me && me.avatar ? me.avatar : '/images/touch-icon-ipad.png'} className={me && me.avatar ? '' : 'guest'} />
                    <figcaption>{(me.name ? '@' + me.name : email) || 'The Soulless One'}</figcaption>
                </figure>
                <FontAwesomeIcon icon={faChevronDown} className="ml-2" />
                <ul className="member-bar-dropdown">{memberBarDropdownLinks}</ul>
            </li>
        ) : (
            <li className="chevron" aria-haspopup="true">
                <figure>
                    <img className="guest" alt="The Soulless One" src="/images/touch-icon-ipad.png" />
                    <figcaption>Welcome, Soulless One!</figcaption>
                </figure>
                <FontAwesomeIcon icon={faChevronDown} className="ml-2" />
                <ul className="member-bar-dropdown">
                    <li key="sign_in">
                        <a href="/oauth/to/discord">
                            <FontAwesomeIcon icon={faSignInAlt} size="2x" fixedWidth /> Login via Discord
                        </a>
                    </li>
                </ul>
            </li>
        );

        return (
            <header className="container">
                <h1 className="col-xs-24 col-md-17 col-lg-12">Lodge of Sorceresses</h1>
                <ul className="member-bar d-none d-md-flex">
                    {memberBarFirstSection}
                    <li className="d-none d-lg-block">
                        <a href="https://lodgeofsorceresses.com" title="Forums" target="_blank" rel='noreferrer'>
                            <FontAwesomeIcon icon={faGlobe} size="lg" /> Forums
                        </a>
                    </li>
                    <li className="d-none d-lg-block">
                        <a href="https://www.esologs.com/guild/autojoin/521/XTk263af" title="ESOLogs" target="_blank" rel='noreferrer'>
                            <FontAwesomeIcon icon={faAnalytics} size="lg" /> ESOLogs
                        </a>
                    </li>
                    <li className="d-none d-lg-block">
                        <a href="https://discord.gg/DQ68UNr" title="Discord" target="_blank" rel='noreferrer'>
                            <FontAwesomeIcon icon={['fab', 'discord']} size="lg" /> Discord
                        </a>
                    </li>
                </ul>
                <nav className="col-md-24">
                    <ul className="nav-tabs">{navLinksRendered}</ul>
                </nav>
                <a title="Realtime application protection"
                    href="https://www.sqreen.com/?utm_source=badge"
                    target="_blank"
                    rel='noreferrer'
                    className='sqreen-badge d-none d-md-block'>
                    <img src="https://s3-eu-west-1.amazonaws.com/sqreen-assets/badges/20171107/sqreen-light-badge.svg"
                        alt="Sqreen | Runtime Application Protection" />
                </a>
            </header>
        );
    };
}

Header.propTypes = {
    history: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    match: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    myCharacters: characters,
    navLinks: PropTypes.array,
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    myCharacters: state.getIn(['myCharacters']),
    notifications: state.getIn(['notifications']),
});

export default withRouter(connect(mapStateToProps)(Header));
