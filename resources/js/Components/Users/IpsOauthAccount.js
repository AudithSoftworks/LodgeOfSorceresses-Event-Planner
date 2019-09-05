import React, { PureComponent } from 'react';
import { connect } from 'react-redux';
import { user } from '../../vendor/data';

class IpsOauthAccount extends PureComponent {
    render = () => {
        const { me } = this.props;

        return me.linkedAccountsParsed.ips ? (
            <article className='jumbotron success ml-2 mr-2'>
                <h3>Your Lodge Forum Account:</h3>
                <p>Linked</p>
                <small className='half-transparent'>If you have problems with your Forum account, please contact the guild leader on Discord for assistance.</small>
            </article>
        ) : (
            <article className='jumbotron warning ml-2 mr-2'>
                <h3>Your Lodge Forum Account:</h3>
                <p>Not Linked</p>
                <small>If you are an actual member of Lodge in-game, you need to register at Lodge Forum as well (by logging in via Discord).</small>
                <small>We are temporarily using the Calendar there (until Planner Calendar is operational).</small>
                <small>If you don't have a Forum account created, <a href='https://lodgeofsorceresses.com' target='_blank'>click here to create one</a> (link opens in a new tab).</small>
                <small>If/Once your Forum account is ready, <a href='/oauth/to/ips'>click here</a> to link it to Planner.</small>
            </article>
        );
    };
}

IpsOauthAccount.propTypes = {
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
)(IpsOauthAccount);
