import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import { faUserShield } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import getContentAction from '../actions/get-content';
import getGroupsAction from '../actions/get-groups';
import getMyCharactersAction from '../actions/get-my-characters';
import getSetsAction from '../actions/get-sets';
import getSkillsAction from '../actions/get-skills';
import getUserAction from '../actions/get-user';
import { errorsAction } from '../actions/notifications';
import Loading from '../Components/Loading';
import Notification from '../Components/Notification';
import { authorizeUser } from '../helpers';
import { characters, user } from '../vendor/data';

library.add(faDiscord, faUserShield);

class Init extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            authChecked: false,
        };
    };

    componentDidMount = () => {
        const { me, groups, myCharacters, sets, skills, content } = this.props;
        if (me && groups && myCharacters && sets && skills && content) {
            this.setState({ authChecked: true });
        } else if (!groups) {
            this.props.getGroupsAction()
                .then(() => {
                    if (!me) {
                        this.props.getUserAction()
                            .then(() => {
                                this.setState({ authChecked: true });
                                if (authorizeUser(this.props)) {
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
                                } else {
                                }
                            });
                    }
                });
        }
    };

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    };

    renderPrechecksFailedNotification = () => {
        const { dispatch } = this.props;
        const message = [
            <Fragment key="f-1">Pre-checks failed! First, make sure your ESO ID is set!</Fragment>,
            <Fragment key="f-2">Then, make sure you have <b>Soulshriven</b> or any member tag (<b>Initiate</b> etc) on Lodge Discord server.</Fragment>,
            <Fragment key="f-3">Please contact guild leader on Discord if you need help with these issues.</Fragment>,
            <Fragment key="f-4">You won't be able to use Planner until they are addressed!</Fragment>,
        ].reduce((acc, curr) => [acc, ' ', curr]);
        dispatch(
            errorsAction(
                message,
                {
                    container: 'bottom-center',
                    animationIn: ['animated', 'bounceInDown'],
                    animationOut: ['animated', 'bounceOutDown'],
                    dismiss: { duration: 60000 },
                    width: 450,
                },
                'access-denied'
            )
        );
    };

    renderForm = () => {
        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" onSubmit={this.handleSubmit} key="characterCreationForm">
                <h2 className="form-title col-md-24 text-center pr-0 mt-md-5 mb-md-5" title="Login">Login</h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <fieldset className="form-group col-md-8 col-xl-4">

                </fieldset>
                <fieldset className="form-group col-md-24 text-center">
                    <a href="/oauth/to/discord" style={{ backgroundColor: '#8ea1e1', borderColor: 'transparent' }} className="btn btn-info btn-sm mr-2">
                        <FontAwesomeIcon icon={['fab', 'discord']} className='mr-1' /> Authenticate via Discord
                    </a>
                </fieldset>
            </form>
        );
    };

    render = () => {
        const { groups, location, me, myCharacters, sets, skills, content } = this.props;
        if (!this.state.authChecked) {
            return [<Loading key="loading" message="Checking session..." />, <Notification key="notifications" />];
        } else if (me === null) {
            return [this.renderForm(), <Notification key="notifications" />];
        } else {
            if (groups === null) {
                return [<Loading key="loading" message="Fetching groups data..." />, <Notification key="notifications" />];
            } else if (me.linkedAccountsParsed && me.linkedAccountsParsed.discord && !authorizeUser(this.props)) {
                this.renderPrechecksFailedNotification();
            } else if (authorizeUser(this.props)) {
                if (!sets) {
                    return [<Loading key="loading" message="Fetching sets..." />, <Notification key="notifications" />];
                } else if (!skills) {
                    return [<Loading key="loading" message="Fetching skills..." />, <Notification key="notifications" />];
                } else if (!content) {
                    return [<Loading key="loading" message="Fetching content data..." />, <Notification key="notifications" />];
                } else if (!myCharacters) {
                    return [<Loading key="loading" message="Fetching your Characters..." />, <Notification key="notifications" />];
                }
            }

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
    axiosCancelTokenSource: state.getIn(["axiosCancelTokenSource"]),
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
