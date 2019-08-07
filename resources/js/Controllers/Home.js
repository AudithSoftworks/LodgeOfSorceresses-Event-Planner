import * as Calendar from '../Components/Events/Calendar';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { Fragment, PureComponent } from 'react';
import { connect } from 'react-redux';
import { Redirect } from 'react-router-dom';
import { errorsAction } from '../actions/notifications';
import { user } from '../vendor/data';
import Notification from '../Components/Notification';

library.add(faDiscord);

class Home extends PureComponent {
    componentWillUnmount = () => {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Unmount');
    };

    renderActionList = associatedDiscordAccount => {
        let discordLink = (
            <a href="/oauth/to/discord" className="ne-corner danger" title="Discord not linked!">
                <FontAwesomeIcon icon={['fab', 'discord']} />
            </a>
        );
        if (associatedDiscordAccount) {
            discordLink = (
                <a className="ne-corner success" title="Discord linked!">
                    <FontAwesomeIcon icon={['fab', 'discord']} />
                </a>
            );
        }
        const actionList = {
            create: discordLink,
        };
        let actionListRendered = [];
        for (const [actionType, link] of Object.entries(actionList)) {
            if (link) {
                actionListRendered.push(<li key={actionType}>{link}</li>);
            }
        }
        return actionListRendered;
    };

    renderDiscordNotLinkedNotification = () => {
        const { me, notifications } = this.props;
        if (me && !me.linkedAccountsParsed.discord && notifications.find(n => n.key === 'no-discord-account-linked') === undefined) {
            const message = [
                <Fragment key="f-1">
                    <b>Discord not linked!</b> You won't be able to use Planner unless this issue is addressed. Click
                </Fragment>,
                <FontAwesomeIcon icon={['fab', 'discord']} key="icon" />,
                <Fragment key="f-2">icon to the top right, to fix this.</Fragment>,
            ].reduce((prev, curr) => [prev, ' ', curr]);
            this.props.dispatch(
                errorsAction(
                    message,
                    {
                        container: 'bottom-center',
                        animationIn: ['animated', 'bounceInDown'],
                        animationOut: ['animated', 'bounceOutDown'],
                        dismiss: { duration: 30000 },
                        width: 350,
                    },
                    'no-discord-account-linked'
                )
            );
        }
    };

    render = () => {
        const { me, location } = this.props;
        if (!me) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }
        this.renderDiscordNotLinkedNotification();
        const actionListRendered = this.renderActionList(me.linkedAccountsParsed.discord);

        return [
            <section className="col-md-24 p-0 mb-4" key="dashboard">
                <h2 className="form-title col-md-24" title="Dashboard">
                    Dashboard
                </h2>
                <ul className="ne-corner">{actionListRendered}</ul>
                <Calendar.Month />
            </section>,
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
    notifications: PropTypes.array,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    notifications: state.getIn(['notifications']),
});

export default connect(mapStateToProps)(Home);
