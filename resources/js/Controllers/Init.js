import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import { faUserShield } from '@fortawesome/pro-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import getContentAction from '../actions/get-content';
import getMyCharactersAction from '../actions/get-my-characters';
import getSetsAction from '../actions/get-sets';
import getSkillsAction from '../actions/get-skills';
import { errorsAction } from '../actions/notifications';
import Loading from '../Components/Loading';
import Notification from '../Components/Notification';
import { authorizeUser } from '../helpers';
import { characters, user } from '../vendor/data';

library.add(faDiscord, faUserShield);

class Init extends PureComponent {
    componentDidMount = () => {
        const { myCharacters, sets, skills, content } = this.props;
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

    renderPrechecksFailedNotification = () => {
        const { dispatch } = this.props;
        const message = [
            <Fragment key="f-1">Pre-check failed! Make sure you have <b>Soulshriven</b> or <b>Member</b> tag on Lodge Discord server.</Fragment>,
            <Fragment key="f-3">Please contact guild leader on Discord if you need a help.</Fragment>,
            <Fragment key="f-4">You won't be able to use Guild Planner until this is addressed!</Fragment>,
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

    renderLoginForm = () => {
        this.setRedirectUri(this.props.location);

        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" onSubmit={this.handleSubmit} key="characterCreationForm">
                <h2 className="form-title col-md-24 text-center pr-0 mt-md-5 mb-md-5" title="Login">Login</h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <fieldset className="form-group col-md-24 text-center">
                    <a href="/oauth/to/discord" style={{ backgroundColor: '#8ea1e1', borderColor: 'transparent' }} className="btn btn-info btn-sm mr-2">
                        <FontAwesomeIcon icon={['fab', 'discord']} /> Login via Discord
                    </a>
                </fieldset>
            </form>
        );
    };

    render = () => {
        const { me, myCharacters, sets, skills, content } = this.props;
        if (me === null) {
            return [this.renderLoginForm()];
        }

        if (authorizeUser(this.props)) {
            if (!sets || !skills || !content || !myCharacters) {
                this.setRedirectUri(this.props.location);
                return [<Loading key="loading" message="Loading data..." />, <Notification key="notifications" />];
            }
        }

        let redirectUri = localStorage.getItem('redirectUri');
        localStorage.removeItem('redirectUri');
        if (!redirectUri || redirectUri === '') {
            redirectUri = '/dashboard';
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
    groups: PropTypes.object,
    sets: PropTypes.array,
    skills: PropTypes.array,
    myCharacters: characters,
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
    getSetsAction: () => dispatch(getSetsAction()),
    getSkillsAction: () => dispatch(getSkillsAction()),
    getContentAction: () => dispatch(getContentAction()),
    getMyCharactersAction: () => dispatch(getMyCharactersAction()),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Init);
