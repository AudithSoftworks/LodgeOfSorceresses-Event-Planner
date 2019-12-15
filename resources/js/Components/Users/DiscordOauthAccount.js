import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { user } from '../../vendor/data';

class DiscordOauthAccount extends PureComponent {
    render = () => {
        const { me } = this.props;

        return me.linkedAccountsParsed && me.linkedAccountsParsed.discord ? (
            <article className='jumbotron success ml-2 mr-2' data-cy='account-status-element'>
                <h3>Your Discord Account:</h3>
                <p>Linked</p>
                <small className='half-transparent'>If you have used a wrong Discord account, please contact the guild leader on Discord for assistance.</small>
            </article>
        ) : (
            <article className='jumbotron danger ml-2 mr-2' data-cy='account-status-element'>
                <h3>Your Discord Account:</h3>
                <p>Not Linked</p>
                <small>You won't be able to use Planner, until Discord is linked to it. <a href='/oauth/to/discord'>Click here</a> to fix this problem.</small>
            </article>
        );
    };
}

DiscordOauthAccount.propTypes = {
    me: user,
};

const mapStateToProps = state => ({
    me: state.getIn(['me']),
    notifications: state.getIn(['notifications']),
});

const mapDispatchToProps = dispatch => ({
    dispatch,
});

export default connect(
    mapStateToProps,
    mapDispatchToProps
)(DiscordOauthAccount);
