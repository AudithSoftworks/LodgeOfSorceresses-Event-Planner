import(/* webpackPrefetch: true, webpackChunkName: "dashboard-scss" */ '../../../sass/_dashboard.scss');

import PropTypes from "prop-types";
import React, { Fragment, PureComponent } from "react";
import { connect } from "react-redux";
import { Link, Redirect } from "react-router-dom";
import Notification from "../../Components/Notification";
import { authorizeUser } from "../../helpers";
import { characters, user } from "../../vendor/data";

class Home extends PureComponent {
    constructor(props) {
        super(props);
        this.authorizeUser = authorizeUser.bind(this);
    }

    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    }

    render = () => {
        const { me, location, myCharacters } = this.props;
        if (!me || !myCharacters || !this.authorizeUser(true)) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }

        return [
            <section className="col-md-13 col-lg-17 p-0 mb-4 dashboard" key="dashboard">
                <h2 className="form-title col-md-24 pr-5" title="Welcome!">
                    Welcome, {me.name || 'Soulless One'}!
                </h2>
                <h3 className='col-md-24 mt-2'>My Account at a Glance</h3>
                <dl className={me.isMember ? 'members' : 'soulshriven'}>
                    <dt>Account Type</dt>
                    <dd>{
                        me.isMember
                            ? [<Fragment key='item-1'>Member</Fragment>, <small key='item-2'>[<Link to='/onboarding/soulshriven'>switch</Link>]</small>]
                            : [<Fragment key='item-1'>Soulshriven</Fragment>, <small key='item-2'>[<Link to='/onboarding/members'>switch</Link>]</small>]
                    }</dd>
                </dl>
                <dl className={me.linkedAccountsParsed.ips ? 'info' : 'danger'}>
                    <dt>Forum Account Linked</dt>
                    <dd>{
                        me.linkedAccountsParsed.ips
                            ? [<Fragment key='item-1'>Yes</Fragment>, <small key='item-2'>[<a href='/oauth/to/ips'>refresh it</a>]</small>]
                            : [<Fragment key='item-1'>No</Fragment>, <small key='item-2'>[<a href='/oauth/to/ips'>link now</a>]</small>]
                    }</dd>
                </dl>
                <dl className={me.characters.length > 0 ? 'info' : 'danger'}>
                    <dt># of characters</dt>
                    <dd>{me.characters.length} <small>[<Link to='/@me/characters'>manage</Link>]</small>
                    </dd>
                </dl>
                <dl className={me.clearanceLevel ? me.clearanceLevel.slug : 'danger'}>
                    <dt>Overall Rank</dt>
                    <dd>{
                        me.clearanceLevel
                            ? me.clearanceLevel.rank.title
                            : [<Fragment key='item-1'>None</Fragment>, <small key='item-2'>[<Link to='/@me/characters'>get going</Link>]</small>]
                    }</dd>
                </dl>
                <h3 className='col-md-24 mt-5'>My Attendances</h3>
            </section>,
            <aside key='member-onboarding'
                   data-heading='Here you can ...'
                   data-text={
                       '* Link forum account to Guild Planner\u000A'
                       + '* Switch your membership mode\u000A'
                       + '* Track your Attendance'
                   }
                   className='banner col-md-11 col-lg-7 d-none d-sm-inline-block'
            />,
            <Notification key="notifications" />,
        ];
    };
}

Home.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    myCharacters: characters,
    notifications: PropTypes.array,

    dispatch: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(["axiosCancelTokenSource"]),
    me: state.getIn(["me"]),
    myCharacters: state.getIn(["myCharacters"]),
    notifications: state.getIn(["notifications"]),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Home);
