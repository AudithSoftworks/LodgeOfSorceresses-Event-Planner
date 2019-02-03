import PropTypes from 'prop-types';
import React, { Component } from 'react';
import Axios from '../vendor/Axios';

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
                this.setState({
                    setsLoaded: true,
                    events: response.data,
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
            console.log(events);
            // const parsedEvents = Object.values(events).map(
            //     item => {
            //         const start = moment(item.start_time);
            //         const eventDate = start.format('MMMM D, YYYY');
            //         const eventDayOfWeek = start.format('dddd');
            //         let eventTime = 'all day';
            //         if (start.format('HH:mm:ss') !== '00:00:00') {
            //             eventTime = start.format('HH:mma');
            //             if (item.end_time) {
            //                 eventTime += moment(item.end_time).filter('[ - ]HH:mma')
            //             }
            //         }
            //
            //         delete(item.start_time);
            //         delete(item.end_time);
            //         item.eventDate = eventDate;
            //         item.eventDayOfWeek = eventDayOfWeek;
            //         item.eventTime = eventTime;
            //
            //         return item;
            //     }
            // );
            // console.log(parsedEvents);
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

Events.propTypes = {
    events: PropTypes.shape({
        start_time: PropTypes.string,
        end_time: PropTypes.string
    })
};

export default Events;
