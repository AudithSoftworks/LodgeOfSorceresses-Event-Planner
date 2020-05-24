import React from "react";

export const authorizeUser = function (withAdditionalPrechecks = false) {
    const { me } = this.props;

    if (!me) {
        return false;
    }

    if (withAdditionalPrechecks && (!me.name || !me.name.length)) {
        return false;
    }

    return me.isMember || me.isSoulshriven;
};

export const authorizeTeamManager = ({ me, team }) => {
    return me.id === team.led_by.id || me.id === team.created_by.id || me.isAdmin;
};

export const renderActionList = actionList => {
    const actionListRendered = [];
    for (const [actionType, link] of Object.entries(actionList)) {
        if (link) {
            actionListRendered.push(<li key={actionType}>{link}</li>);
        }
    }

    return actionListRendered;
};

export const deleteMyCharacter = function (event) {
    event.preventDefault();
    if (confirm("Are you sure you want to delete this character?")) {
        const currentTarget = event.currentTarget;
        const characterId = parseInt(currentTarget.getAttribute("data-id"));
        if (characterId) {
            const { deleteMyCharacterAction } = this.props;
            deleteMyCharacterAction(characterId);
        }
    }
};

export const deleteTeam = function (event) {
    event.preventDefault();
    if (confirm("Are you sure you want to delete this team?")) {
        const currentTarget = event.currentTarget;
        const teamId = parseInt(currentTarget.getAttribute("data-id"));
        if (teamId) {
            const { deleteTeamAction } = this.props;
            deleteTeamAction(teamId);
        }
    }
};

export const rerankCharacter = function (event) {
    event.preventDefault();
    if (confirm("Are you sure you want to **Rerank** this Character?")) {
        const currentTarget = event.currentTarget;
        const characterId = parseInt(currentTarget.getAttribute("data-id"));
        const action = currentTarget.getAttribute("data-action");
        const { putCharacterAction } = this.props;
        putCharacterAction(characterId, { action });
    }
};

export const filter = function (event, typeUpdating) {
    const temp = Object.assign({}, this.state.filters);
    for (const [type, value] of Object.entries(temp)) {
        if (type === typeUpdating) {
            temp[type] = !value;
            event.currentTarget.classList.toggle("inactive");
        } else {
            temp[type] = value;
        }
    }
    this.setState({
        filters: temp,
    });
};
