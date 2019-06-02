import(
    /* webpackPrefetch: true */
    /* webpackChunkName: "header-scss" */
    '../../../sass/_header.scss'
    );

import { library } from '@fortawesome/fontawesome-svg-core';
import { faHome, faUsers, faUsersCog } from '@fortawesome/pro-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { NavLink, withRouter } from 'react-router-dom';
import { characters, user } from '../../vendor/data';

library.add(faHome, faUsers, faUsersCog);

class Header extends Component {
    authorize = ({ me, groups }) => {
        if (!me || !groups || !me.linkedAccountsParsed.ips) {
            return;
        }

        const myGroup = Object.entries(groups).find(group => me.linkedAccountsParsed.ips.remote_primary_group === group[1]['ipsGroupId']);

        return myGroup === undefined ? false : myGroup['1']['isAdmin'];
    };

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
        const { myCharacters } = this.props;
        const navLinks = [];
        if (myCharacters) {
            navLinks.push(
                <NavLink exact to="/dashboard" activeClassName="active" title="Home">
                    <FontAwesomeIcon icon="home" size="lg" />
                    <span className='d-none d-sm-inline-block'>Home</span>
                </NavLink>
            );
            navLinks.push(
                <NavLink to="/characters" activeClassName="active" title="My Characters">
                    <FontAwesomeIcon icon="users" size="lg" />
                    <span className='d-none d-sm-inline-block'>My Characters</span>
                </NavLink>
            );
        }
        if (this.authorize(this.props)) {
            navLinks.push(
                <NavLink to="/admin" activeClassName="active" className="pull-right" title="Officer">
                    <FontAwesomeIcon icon="users-cog" size="lg" />
                    <span className='d-none d-sm-inline-block'>Officer</span>
                </NavLink>
            );
        }

        const navLinksRendered = this.renderNavLinks(navLinks);

        return (
            <header className="container">
                <h1 className="col-xs-24 col-md-18">Lodge of Sorceresses</h1>
                <ul className="member-bar d-none">
                    <li>
                        <figure>
                            <img alt="" src="" />
                        </figure>
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
