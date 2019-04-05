import React, { Fragment, PureComponent } from 'react';
import { Link } from "react-router-dom";
import Notification from "../Notification";

class Home extends PureComponent {
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
                <article>
                    <h3>DPS Parses</h3>
                    <ul>
                        <li><Link to='/admin/parses' title='Approve Parses'>Approve Parses</Link></li>
                    </ul>
                </article>

                {this.renderFlashMessages()}
                <Notification key='notifications' messages={messages} options={options}/>
            </section>
        ];
    };
}

export default Home;
