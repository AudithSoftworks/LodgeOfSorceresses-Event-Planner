export const teams = cy => {
    cy.fixture('teams.json').as('teams');
    cy.route({
        method: 'GET',
        url: '/api/teams',
        response: '@teams',
    }).as('loadTeams');
};
