import React from "react";

export const authorizeUser = function (withAdditionalPrechecks = false) {
    const { me, groups } = this.props;

    if (!me || !groups || !me.linkedAccountsParsed || !me.linkedAccountsParsed.discord) {
        return false;
    }

    if (withAdditionalPrechecks && (!me.name || !me.name.length)) {
        return false;
    }

    const discordGroups = me.linkedAccountsParsed.discord.remote_secondary_groups;
    if (discordGroups && discordGroups.length) {
        const listOfUserGroups = discordGroups.find(discordRole => {
            const matchingGroup = Object.entries(groups).find(group => discordRole === group[1]["discordRole"]);

            return matchingGroup === undefined ? false : matchingGroup["1"]["isMember"];
        });

        return listOfUserGroups !== undefined;
    }

    return false;
};

export const authorizeAdmin = function () {
    const { me, groups } = this.props;

    if (!me || !groups || !me.linkedAccountsParsed || !me.linkedAccountsParsed.ips) {
        return false;
    }

    const myGroup = Object.entries(groups).find(group => me.linkedAccountsParsed.ips.remote_primary_group === group[1]["ipsGroupId"]);

    return !(!myGroup || !myGroup[1]["isAdmin"]);
};

export const authorizeTeamManager = ({ me, team, authorizedAsAdmin }) => {
    return me.id === team.led_by.id || me.id === team.created_by.id || authorizedAsAdmin;
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

export const deleteTeamMembership = function (event) {
    event.preventDefault();
    if (confirm("Are you sure you want to remove this character from the team?")) {
        const currentTarget = event.currentTarget;
        const characterId = parseInt(currentTarget.getAttribute("data-id"));
        if (characterId) {
            const { deleteTeamMembershipAction } = this.props;
            deleteTeamMembershipAction(characterId);
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
