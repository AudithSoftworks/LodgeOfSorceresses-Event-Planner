import React, { Fragment, PureComponent } from 'react';
import { Link } from "react-router-dom";
import Axios from "../../vendor/Axios";
import Notification from "../Notification";

class Home extends PureComponent {
    componentDidMount() {
        this.cancelTokenSource = Axios.CancelToken.source();
        Axios.get('/api/admin/', {
            cancelToken: this.cancelTokenSource.token
        }).then((response) => {
            if (response.data) {
                this.cancelTokenSource = null;
            }
        }).catch((error) => {
            if (!Axios.isCancel(error)) {
                this.setState({
                    messages: [
                        {
                            type: "danger",
                            message: error.response.statusText
                        }
                    ]
                });
                if (error.response && error.response.status === 403) {
                    this.props.history.push('/', this.state);
                }
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
        const messages = [
            {
                type: 'default',
                message: [
                    <Fragment key='f-1'>Dashboard coming soon!</Fragment>,
                ].reduce((prev, curr) => [prev, ' ', curr])
            }
        ];
        const options = {
            container: 'bottom-center',
            animationIn: ["animated", "bounceInDown"],
            animationOut: ["animated", "bounceOutDown"],
            dismiss: {duration: 30000},
        };

        return [
            <section className="col-md-24 p-0 mb-4" key='characterList'>
                <h2 className="form-title col-md-24">Dashboard</h2>
                <article className='col-md-24'>
                    <h3>Available actions</h3>
                    <ul>
                        <li><Link to='/admin/parses' title='Approve Parses'>DPS Parses pending Approval</Link></li>
                    </ul>
                </article>

                {this.renderFlashMessages()}
                <Notification key='notifications' messages={messages} options={options}/>
            </section>
        ];
    };
}

export default Home;
