import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import getContentAction from "../actions/get-content";
import getGroupsAction from '../actions/get-groups';
import getMyCharactersAction from '../actions/get-my-characters';
import getSetsAction from '../actions/get-sets';
import getSkillsAction from '../actions/get-skills';
import getUserAction from '../actions/get-user';
import { characters, user } from '../vendor/data';
import Loading from '../Components/Loading';
import Notification from '../Components/Notification';

library.add(faDiscord);

class Init extends PureComponent {
    componentDidMount = () => {
        const { me, groups, myCharacters, sets, skills, content } = this.props;
        if (!me) {
            this.props.getUserAction();
        }
        if (!groups) {
            this.props.getGroupsAction();
        }
        if (!myCharacters) {
            this.props.getMyCharactersAction();
        }
        if (!sets) {
            this.props.getSetsAction();
        }
        if (!skills) {
            this.props.getSkillsAction();
        }
        if (!content) {
            this.props.getContentAction();
        }
    };

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    };

    render = () => {
        const { associatedDiscordAccount, location, myCharacters, sets, skills, content } = this.props;
        if (associatedDiscordAccount === null) {
            return [<Loading key="loading" message="Fetching account details..." />, <Notification key="notifications" />];
        } else if (sets === null) {
            return [<Loading key="loading" message="Fetching Sets..." />, <Notification key="notifications" />];
        } else if (skills === null) {
            return [<Loading key="loading" message="Fetching Skills..." />, <Notification key="notifications" />];
        } else if (content === null) {
            return [<Loading key="loading" message="Fetching Content Data..." />, <Notification key="notifications" />];
        } else if (myCharacters === null) {
            return [<Loading key="loading" message="Fetching your Characters..." />, <Notification key="notifications" />];
        } else {
            let redirectUri = '/dashboard';
            if (location.state && location.state.prevPath) {
                redirectUri = location.state.prevPath;
            }

            return <Redirect to={redirectUri} />;
        }
    };
}

Init.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    associatedDiscordAccount: PropTypes.array,
    me: user,
    groups: PropTypes.object,
    sets: PropTypes.array,
    skills: PropTypes.array,
    myCharacters: characters,
    getUserAction: PropTypes.func.isRequired,
    getGroupsAction: PropTypes.func.isRequired,
    getSetsAction: PropTypes.func.isRequired,
    getSkillsAction: PropTypes.func.isRequired,
    getContentAction: PropTypes.func.isRequired,
    getMyCharactersAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    groups: state.getIn(['groups']),
    sets: state.getIn(['sets']),
    skills: state.getIn(['skills']),
    content: state.getIn(['content']),
    myCharacters: state.getIn(['myCharacters']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    getUserAction: () => dispatch(getUserAction()),
    getGroupsAction: () => dispatch(getGroupsAction()),
    getSetsAction: () => dispatch(getSetsAction()),
    getSkillsAction: () => dispatch(getSkillsAction()),
    getContentAction: () => dispatch(getContentAction()),
    getMyCharactersAction: () => dispatch(getMyCharactersAction()),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Init);
