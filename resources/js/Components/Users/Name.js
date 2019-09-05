import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import putUserAction from '../../actions/put-user';
import { user } from '../../vendor/data';

class Name extends PureComponent {
    handleSubmit = event => {
        event.preventDefault();
        const { putUserAction } = this.props;
        const data = new FormData(event.target);

        return putUserAction(data);
    };

    renderUpdateNameForm = () => (
        <form className="jumbotron danger ml-2 mr-2" onSubmit={this.handleSubmit} key="update-name-form">
            <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]').getAttribute('content')} />
            <h3>Your ESO ID:</h3>
            <input type='text' name='name' required placeholder='e.g. Glevissig (Gelmir)' />
            <input type='submit' value='Save' className='btn btn-info' />
            <small>Enter your ESO ID exactly as it is (omit @ sign). Feel free to add an easy-to-pronounce nickname for yourself (to be called with) in parentheses.</small>
        </form>
    );

    renderName = user => (
        <article className='jumbotron success ml-2 mr-2'>
            <h3>Your ESO ID:</h3>
            <p>{'@' + user.name}</p>
            <small className='half-transparent'>To update your ESO ID, please contact the guild leader on Discord.</small>
        </article>
    );

    render = () => {
        const { me } = this.props;

        return me.name && me.name.length ? this.renderName(me) : this.renderUpdateNameForm();
    };
}

Name.propTypes = {
    me: user,

    putUserAction: PropTypes.func.isRequired,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
    putUserAction: data => dispatch(putUserAction(data)),
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(Name);
