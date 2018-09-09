import Axios from '../vendor/Axios';
import React, { Component } from 'react';

class Events extends Component {
    constructor(props) {
        super(props);
        this.state = {
            setsLoaded: false,
            events: [],
            error: null
        };
    }

    componentDidMount = () => {
        Axios
            .get('/api/events')
            .then((response) => {
                console.log(response.data.events);
                this.setState({
                    setsLoaded: true,
                    events: response.data.events,
                    error: null
                });
            })
            .catch(function (error) {
                this.setState({
                    setsLoaded: true,
                    events: [],
                    error: error
                });
            });
    };

    render = () => {
        const {setsLoaded, events, error} = this.state;
        if (error) {
            return <fieldset className='error'>Error</fieldset>;
        } else if (!setsLoaded) {
            return <fieldset className='general'>Loading</fieldset>;
        } else {
            const events = Object.values(events).map(
                item => console.log(item)
            );

            return (
                <section className="col-md-12">
                    <h2 className="form-title font-green col-md-12">Calendar Events</h2>
                    <ul>
                        <li><a href="EVENT URL">EVENT NAME</a></li>
                    </ul>
                </section>
            );
        }
    };
}

export default Events;
