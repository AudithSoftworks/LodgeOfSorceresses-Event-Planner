export const stubFetchingTeams = cy => {
    cy.fixture('teams.json').as('teams');
    cy.route('GET', '/api/teams', '@teams');
};
