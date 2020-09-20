import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import getContentAction from '../actions/get-content';
import getMyCharactersAction from '../actions/get-my-characters';
import getSetsAction from '../actions/get-sets';
import getSkillsAction from '../actions/get-skills';
import getTeamsAction from '../actions/get-teams';
import { errorsAction } from '../actions/notifications';
import Loading from '../Components/Loading';
import Notification from '../Components/Notification';
import { authorizeUser } from '../helpers';
import { characters, content, teams, user } from '../vendor/data';

class Init extends PureComponent {
    constructor(props) {
        super(props);
        this.authorizeUser = authorizeUser.bind(this);
    }

    componentDidMount = () => {
        const { myCharacters, sets, skills, content, teams } = this.props;
        if (this.authorizeUser(true)) {
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
            if (!teams) {
                this.props.getTeamsAction();
            }
        } else {
            this.renderFlashMessages();
        }
    };

    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    };

    setRedirectUri = location => {
        if (!localStorage.getItem('redirectUri') && location.state && location.state.prevPath) {
            localStorage.setItem('redirectUri', location.state.prevPath);
        }
    };

    renderLoginForm = () => {
        this.setRedirectUri(this.props.location);

        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" key="loginForm">
                <h2 className="form-title col-md-24 text-center pl-0 pr-0 mt-md-5 mb-md-5" title="Login">
                    Login
                </h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <fieldset className="form-group col-md-24 text-center">
                    <a href="/oauth/to/discord" style={{ backgroundColor: '#8ea1e1', borderColor: 'transparent' }} className="btn btn-info btn-sm mr-2">
                        <FontAwesomeIcon icon={['fab', 'discord']} /> Login via Discord
                    </a>
                </fieldset>
            </form>
        );
    };

    renderFlashMessages = () => {
        const bodyElement = document.querySelector('body');
        const flashMessages = JSON.parse(bodyElement.getAttribute('data-flash-messages'));
        const { dispatch, notifications } = this.props;
        if (flashMessages.length && notifications.find(n => n.key === 'flash-messages') === undefined) {
            dispatch(
                errorsAction(
                    flashMessages,
                    {
                        container: 'bottom-center',
                        animationIn: ['animated', 'bounceInDown'],
                        animationOut: ['animated', 'bounceOutDown'],
                        dismiss: { duration: 30000 },
                        width: 320,
                    },
                    'flash-messages',
                ),
            );
        }
    };

    render = () => {
        const { me, myCharacters, sets, skills, content, teams } = this.props;
        if (me === null) {
            return [this.renderLoginForm(), <Notification key="notifications" />];
        }

        if (!this.authorizeUser(true)) {
            return <Redirect to="/home" />;
        }

        if (!sets || !skills || !content || !myCharacters || !teams) {
            this.setRedirectUri(this.props.location);
            return [<Loading key="loading" message="Loading data..." />, <Notification key="notifications" />];
        }

        let redirectUri = localStorage.getItem('redirectUri');
        localStorage.removeItem('redirectUri');
        if (!redirectUri || redirectUri === '') {
            redirectUri = '/@me';
        }

        return <Redirect to={redirectUri} />;
    };
}

Init.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    myCharacters: characters,
    notifications: PropTypes.array,
    sets: PropTypes.array,
    skills: PropTypes.array,
    content,
    teams,

    dispatch: PropTypes.func.isRequired,
    getContentAction: PropTypes.func.isRequired,
    getMyCharactersAction: PropTypes.func.isRequired,
    getSetsAction: PropTypes.func.isRequired,
    getSkillsAction: PropTypes.func.isRequired,
    getTeamsAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(['axiosCancelTokenSource']),
    content: state.getIn(['content']),
    me: state.getIn(['me']),
    myCharacters: state.getIn(['myCharacters']),
    notifications: state.getIn(['notifications']),
    sets: state.getIn(['sets']),
    skills: state.getIn(['skills']),
    teams: state.getIn(['teams']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    getContentAction: () => dispatch(getContentAction()),
    getMyCharactersAction: () => dispatch(getMyCharactersAction()),
    getSetsAction: () => dispatch(getSetsAction()),
    getSkillsAction: () => dispatch(getSkillsAction()),
    getTeamsAction: () => dispatch(getTeamsAction()),
});

export default connect(mapStateToProps, mapDispatchToProps)(Init);
