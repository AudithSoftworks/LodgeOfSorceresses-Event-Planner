import { faPortrait, faTrashAlt, faUserEdit } from '@fortawesome/pro-light-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import PropTypes from 'prop-types';
import React, { PureComponent } from 'react';
import { Link } from 'react-router-dom';
import { renderActionList } from "../../../helpers";
import { team, user } from '../../../vendor/data';

class Item extends PureComponent {
    canIManageThisTeam = ({me, team, authorizedAsAdmin}) => {
        return me.id === team.led_by.id || me.id === team.created_by.id || authorizedAsAdmin;
    };

    render = () => {
        const { team, onDeleteHandler } = this.props;
        team.actionList = {
            view: (
                <Link to={'/teams/' + team.id} title="Team Details">
                    <FontAwesomeIcon icon={faPortrait} />
                </Link>
            ),
            edit:
                this.canIManageThisTeam(this.props) ? (
                    <Link to={'/teams/' + team.id + '/edit'} title="Edit Team">
                        <FontAwesomeIcon icon={faUserEdit} />
                    </Link>
                ) : null,
            delete:
                typeof onDeleteHandler === 'function' && team.members.length === 0 && this.canIManageThisTeam(this.props) ? (
                    <Link to="#" onClick={onDeleteHandler} data-id={team.id} title="Delete Team">
                        <FontAwesomeIcon icon={faTrashAlt} />
                    </Link>
                ) : null,
        };

        let rowBgColor = 'no-clearance';
        if (team['tier'] !== 0) {
            rowBgColor = 'tier-' + team['tier'];
        }

        return (
            <tr className={rowBgColor} key={'teamRow-' + team.id} data-id={team.id}>
                <td>
                    {team.name}
                    <br/>
                    <small>{'Tier-' + team.tier} / {team.members.length + ' members'}</small>
                </td>
                <td>
                    <ul className="action-list">{renderActionList(team.actionList)}</ul>
                </td>
            </tr>
        );
    };
}

Item.propTypes = {
    me: user,
    authorizedAsAdmin: PropTypes.bool,
    team,
    onDeleteHandler: PropTypes.func, // based on existense of this param, we render Delete button
};

export default Item;
