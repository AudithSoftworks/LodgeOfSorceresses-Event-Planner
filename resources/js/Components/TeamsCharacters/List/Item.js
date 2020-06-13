import { faBadgeCheck, faPortrait, faTrashAlt } from "@fortawesome/pro-light-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import PropTypes from "prop-types";
import React, { PureComponent } from "react";
import { Link } from "react-router-dom";
import { renderActionList } from "../../../helpers";
import { character, team, user } from "../../../vendor/data";

class Item extends PureComponent {
    render = () => {
        const { authorizedTeamManager, character, deleteTeamMembershipHandler, me, team } = this.props;
        const actionList = {
            accept_terms:
                character.owner.id === me.id && !character.team_membership.status ? (
                    <Link to={"/teams/" + team.id + "/characters/" + character.id} title="Accept Invitation">
                        <FontAwesomeIcon icon={faBadgeCheck} />
                    </Link>
                ) : null,
            view: (
                <Link to={"/characters/" + character.id} title="Character Sheet">
                    <FontAwesomeIcon icon={faPortrait} />
                </Link>
            ),
            delete:
                typeof deleteTeamMembershipHandler === "function" && (authorizedTeamManager || character.owner.id === me.id) ? (
                    <Link to="#" onClick={deleteTeamMembershipHandler} data-id={character.id} title="Remove Character">
                        <FontAwesomeIcon icon={faTrashAlt} />
                    </Link>
                ) : null,
        };

        return (
            <tr className={"tier-" + character.approved_for_tier} key={"characterRow-" + character.id} data-id={character.id}>
                <td>{"@" + character.owner.name}</td>
                <td>{character.name}</td>
                <td>{character.role + " / " + character.class + " / Tier-" + character.approved_for_tier}</td>
                <td>{character.team_membership.status ? <span className="badge badge-success">Active</span> : <span className="badge badge-warning">Invitation Pending Acceptance</span>}</td>
                <td>
                    <ul className="action-list">{renderActionList(actionList)}</ul>
                </td>
            </tr>
        );
    };
}

Item.propTypes = {
    authorizedTeamManager: PropTypes.bool.isRequired,
    character: character.isRequired,
    deleteTeamMembershipHandler: PropTypes.func, // based on existense of this param, we render Delete button
    me: user.isRequired,
    team: team.isRequired,
};

export default Item;
