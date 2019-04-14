import { library } from '@fortawesome/fontawesome-svg-core';
import { faDiscord } from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import React, { Fragment, PureComponent } from 'react';
import Axios from "../vendor/Axios";
import Notification from "./Notification";

library.add(faDiscord);

class Home extends PureComponent {
    constructor(props) {
        super(props);
        this.state = {
            discordOauthAccount: null,
            messages: [],
        };
    };

    componentDidMount() {
        this.cancelTokenSource = Axios.CancelToken.source();

        Axios.get('/api/discord-oauth-account', {
            cancelToken: this.cancelTokenSource.token
        }).then((response) => {
            this.cancelTokenSource = null;
            if (response.data) {
                const stateObj = {
                    discordOauthAccount: response.data.discordOauthAccount,
                };
                if (!response.data.discordOauthAccount) {
                    stateObj.messages = [
                        {
                            type: 'default',
                            message: [
                                <Fragment key='f-1'>Discord not linked! Notifications are disabled! Click</Fragment>,
                                <FontAwesomeIcon icon={['fab', 'discord']} key='icon'/>,
                                <Fragment key='f-2'>icon to fix this.</Fragment>,
                            ].reduce((prev, curr) => [prev, ' ', curr])
                        }
                    ];
                }
                this.setState(stateObj);
            }
        }).catch(error => {
            if (!Axios.isCancel(error)) {
                this.setState({
                    messages: [
                        {
                            type: "danger",
                            message: error.response.data.message || error.response.statusText
                        }
                    ]
                })
            }
        });
    };

    componentWillUnmount() {
        this.cancelTokenSource && this.cancelTokenSource.cancel('Unmount');
    };

    renderFlashMessages = () => {
        if (this.props.history.location.state && this.props.history.location.state.messages) {
            const messages = this.props.history.location.state.messages;
            this.props.history.location.state.messages = [];
            return <Notification key='flash-notifications' messages={messages}/>;
        }

        return null;
    };

    render = () => {
        const {discordOauthAccount, messages} = this.state;

        const actionList = {
            create: discordOauthAccount
                ? <a className='ne-corner success' title='Discord linked!'><FontAwesomeIcon icon={['fab', 'discord']}/></a>
                : <a href="/oauth/to/discord"
                     className='ne-corner danger'
                     title='Discord not linked!'><FontAwesomeIcon icon={['fab', 'discord']}/></a>
        };
        let actionListRendered = [];
        for (const [actionType, link] of Object.entries(actionList)) {
            if (link) {
                actionListRendered.push(<li key={actionType}>{link}</li>);
            }
        }

        return [
            <section className="col-md-24 p-0 mb-4" key='dashboard'>
                <h2 className="form-title col-md-24">Dashboard</h2>
                <ul className='ne-corner'>{actionListRendered}</ul>
            </section>,
            this.renderFlashMessages(),
            <Notification key='notifications' messages={messages} options={{
                container: 'bottom-center',
                animationIn: ["animated", "bounceInDown"],
                animationOut: ["animated", "bounceOutDown"],
                dismiss: {duration: 30000},
            }}/>
        ];
    };
}

export default Home;
