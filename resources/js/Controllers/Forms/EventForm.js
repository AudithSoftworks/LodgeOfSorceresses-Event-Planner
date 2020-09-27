import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import Select from 'react-select';
import makeAnimated from 'react-select/animated';
import postMyCharacterAction from '../../actions/post-my-character';
import putMyCharacterAction from '../../actions/put-my-character';
import Notification from '../../Components/Notification';
import { characters, content } from '../../vendor/data';

class EventForm extends PureComponent {
    componentWillUnmount() {
        this.props.axiosCancelTokenSource && this.props.axiosCancelTokenSource.cancel('Request cancelled.');
    }

    componentDidUpdate(prevProps, prevState, snapshot) {
        // We had a change in Events data: Redirect!
        if (prevProps.myCharacters.length !== this.props.myCharacters.length) {
            return this.props.history.push('/events');
        }
        const { match } = this.props;
        if (match.params && match.params.id) {
            if (prevProps.myCharacters !== this.props.myCharacters) {
                return this.props.history.push('/events');
            }
        }
    }

    handleSubmit = event => {
        event.preventDefault();
        alert('Coming Soon!');
        // const { match, postMyCharacterAction, putMyCharacterAction } = this.props;
        // const data = new FormData(event.target);
        // if (match.params && match.params.id) {
        //     const characterId = match.params.id;
        //
        //     return putMyCharacterAction(characterId, data);
        // }
        //
        // return postMyCharacterAction(data);
    };

    renderForm = () => {
        const { match, content } = this.props;

        const contentOptions = Object.values(content).map(item => ({
            value: item.id,
            label: item.name + ' (' + item.short_name + ')' + ' ' + (item.version || ''),
        }));

        const heading = (match.params.id ? 'Edit' : 'Create') + ' Event';
        const contentTierAdjustmentOptions = [
            { value: 0, label: 'No, restrict to actual content tier!' },
            { value: -1, label: 'Yes, allow players of 1 tier below.' },
            { value: -4, label: 'Yes, allow players of any tier clearance.' },
        ];
        const autoCheckInOptions = [
            { value: 0, label: 'Regular Event: Attendance is voluntary, members need to check-in manually.' },
            { value: 1, label: 'Mandated Event: Attendance is mandatory, members are automatically checked-in.' },
        ];
        const teamOptions = [
            { value: 1, label: 'Core One' },
            { value: 2, label: 'Core Two' },
            { value: 3, label: 'Core Three' },
        ];
        const animated = makeAnimated();

        return (
            <form className="col-md-24 d-flex flex-row flex-wrap p-0" onSubmit={this.handleSubmit} key="characterCreationForm">
                <h2 className="form-title col-md-24" title={heading}>
                    {heading}
                </h2>
                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
                <fieldset className="form-group col-md-10">
                    <label>Content Type:</label>
                    <Select
                        options={contentOptions}
                        // defaultValue={character ? this.classOptions.filter(option => option.label === character.class) : this.classOptions[0]}
                        components={animated}
                        name="content_id"
                        autoFocus
                    />
                </fieldset>
                <fieldset className="form-group col-md-4">
                    <label htmlFor="start_date" title="Event Start Date">
                        Event Date:
                    </label>
                    <input type="date" name="start_date" id="start_date" />
                </fieldset>
                <fieldset className="form-group col-md-3">
                    <label htmlFor="start_time" title="Event Start Time">
                        Event Time:
                    </label>
                    <input type="time" name="start_time" id="start_time" />
                </fieldset>
                <fieldset className="form-group col-md-7">
                    <label>Ease-up on Tier Clearance?</label>
                    <Select options={contentTierAdjustmentOptions} defaultValue={contentTierAdjustmentOptions[0]} components={animated} name="content_tier_adjustment" />
                </fieldset>
                <fieldset className="form-group col-md-12">
                    <label>Check-in Mode</label>
                    <Select options={autoCheckInOptions} defaultValue={autoCheckInOptions[0]} components={animated} name="auto_check_in" />
                </fieldset>
                <fieldset className="form-group col-md-4">
                    <label>Mandated Team</label>
                    <Select options={teamOptions} components={animated} name="mandated_team_id" />
                </fieldset>
                <fieldset className="form-group col-md-24 text-right">
                    <Link to="/events" className="btn btn-info btn-lg mr-1">
                        Cancel
                    </Link>
                    <button className="btn btn-primary btn-lg" type="submit">
                        Save
                    </button>
                </fieldset>
            </form>
        );
    };

    render = () => {
        const { content } = this.props;
        if (!content) {
            return <Redirect to={{ pathname: '/', state: { prevPath: location.pathname } }} />;
        }

        return [this.renderForm(), <Notification key="notifications" />];
    };
}

EventForm.propTypes = {
    match: PropTypes.object.isRequired,
    location: PropTypes.object.isRequired,
    history: PropTypes.object.isRequired,

    axiosCancelTokenSource: PropTypes.object,
    myCharacters: characters,
    content,
    notifications: PropTypes.array,

    postMyCharacterAction: PropTypes.func.isRequired,
    putMyCharacterAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    axiosCancelTokenSource: state.getIn(['axiosCancelTokenSource']),
    content: state.getIn(['content']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    postMyCharacterAction: data => dispatch(postMyCharacterAction(data)),
    putMyCharacterAction: (characterId, data) => dispatch(putMyCharacterAction(characterId, data)),
});

export default connect(mapStateToProps, mapDispatchToProps)(EventForm);
