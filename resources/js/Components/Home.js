import React, { PureComponent, Fragment } from 'react';
import Notification from "./Notification";

class Home extends PureComponent {
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
                <Notification key='notifications' messages={messages} options={options}/>
            </section>
        ];
    };
}

export default Home;
