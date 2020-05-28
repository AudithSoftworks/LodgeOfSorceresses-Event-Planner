import(/* webpackPrefetch: true, webpackChunkName: "header-scss" */ '../../sass/_cms.scss');

import { Markup } from "interweave";
import PropTypes from "prop-types";
import React, { PureComponent } from "react";
import { connect } from "react-redux";
import { Redirect } from "react-router-dom";
import finalizeOnboardingAction from "../actions/finalize-onboarding";
import { errorsAction } from "../actions/notifications";
import Loading from "../Components/Loading";
import { authorizeUser, transformAnchors } from "../helpers";
import { getOnboardingContentByStep } from "../vendor/api";
import axios from "../vendor/axios";
import { user } from "../vendor/data";
import Notification from "../Components/Notification";

class Onboarding extends PureComponent {
    mapOfSteps = {
        members: [
            'Guild Introduction',
            'General Guild Requirements',
            'Tier-based Content Clearance Model',
            'Endgame Attendance Guidelines for Raid Cores (optional reading)',
        ],
        soulshriven: [
            'Tier-based Content Clearance Model',
            'What is Open Initiative?',
            'Open-Events Organization Guidelines (optional reading)',
        ],
    };

    constructor(props) {
        super(props);
        this.state = {
            data: [],
            step: 1,
        };
        this.authorizeUser = authorizeUser.bind(this);
    };

    getHeadingsOfSteps = (mode) => {
        return this.mapOfSteps[mode];

    };

    componentWillUnmount = () => {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Request cancelled.');
    };

    UNSAFE_componentWillUpdate = nextProps => {
        if (this.props.me !== nextProps.me) {
            return this.props.history.push('/');
        }
    }

    fetchCmsContent = () => {
        const { data, step } = this.state;
        this.cancelTokenSource = axios.CancelToken.source();
        if (!data.length && step < 5) {
            getOnboardingContentByStep(this.cancelTokenSource, this.props.match.params.mode, step)
                .then(data => {
                    this.cancelTokenSource = null;
                    this.setState({ data });
                })
                .catch(error => {
                    if (!axios.isCancel(error)) {
                        const message = error.response.data.message || error.response.statusText || error.message;
                        this.props.dispatch(errorsAction(message));
                    }
                });
        }
    };

    finalizeOnboardingHandler = mode => {
        const { finalizeOnboardingAction } = this.props;
        finalizeOnboardingAction({ mode });
    };

    render = () => {
        const { match, me, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        if (this.authorizeUser()) {
            return <Redirect to={{ pathname: "/dashboard", state: { prevPath: location.pathname } }} />;
        }

        const mode = match.params.mode;
        const contentHeadings = this.getHeadingsOfSteps(mode);
        const numberOfSteps = contentHeadings.length;
        const { data, step } = this.state;
        if (data.length === 0 && step < numberOfSteps + 1) {
            this.fetchCmsContent();

            return [<Loading message="Fetching content..." key="loading" />];
        }
        const currentData = data.shift();

        return [
            <h2 className={'form-title col-md-24' + (step === numberOfSteps + 1 ? ' mt-5 mb-5 text-center' : '')} key='title'>{
                step < numberOfSteps + 1
                    ? 'Step ' + step + '/' + numberOfSteps + ': ' + (contentHeadings[step - 1])
                    : 'Joining the Guild as a ' + (mode === 'members' ? 'Member' : 'Soulshriven')
            }</h2>,
            <article className='col-24 cms-content' key='article'>
                {
                    step < numberOfSteps + 1
                        ? <Markup content={currentData['content']} noWrap={true} transform={transformAnchors} key='content' />
                        : (
                            mode === 'members'
                                ? <article className='text-center pt-5 mb-5'>
                                    <p>I have read all the material provided and understood what guild membership entails.</p>
                                    <p>I want to join Lodge as a Member!</p>
                                </article>
                                : <article className='text-center pt-5 mb-5'>
                                    <p>I have read all the material provided and understood what Open Initiative participation entails.</p>
                                    <p>I want to be part of Open Initiative & participate in open-events!</p>
                                </article>
                        )

                }
            </article>,
            <span className='mt-5 col-24 d-flex flex-row flex-nowrap align-items-center justify-content-center' key='buttons' role='group'>
                <button type='button'
                        onClick={() => this.setState({ step: step - 1 })}
                        disabled={step === 1}
                        className='btn btn-primary btn-lg mr-5'>Back</button>
                {
                    step !== numberOfSteps + 1
                        ? <a href={currentData['url']} target='_blank' className='pl-5 pr-5 ml-5 mr-5'>Read & Discuss this on Forums</a>
                        : null
                }
                <button type='button'
                        onClick={
                            step < numberOfSteps + 1
                                ? () => this.setState({ step: step + 1 })
                                : () => this.finalizeOnboardingHandler(mode)
                        }
                        className='btn btn-primary btn-lg ml-5'>{step < numberOfSteps + 1 ? 'Continue' : 'Accept & Join'}</button>
            </span>,
            <Notification key='notification' />
        ];
    };
}

Onboarding.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    me: user,
    notifications: PropTypes.array,

    dispatch: PropTypes.func.isRequired,
    finalizeOnboardingAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(["me"]),
    notifications: state.getIn(["notifications"]),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    finalizeOnboardingAction: data => dispatch(finalizeOnboardingAction(data)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Onboarding);
