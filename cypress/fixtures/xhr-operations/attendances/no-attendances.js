export const noAttendances = cy => {
    cy.fixture('xhr-data/attendances/no-attendances.json').as('noAttendances');
    cy.route({
        method: 'GET',
        url: '/api/attendances/6',
        response: '@noAttendances'
    }).as('loadAttendances');
    cy.route({
        method: 'GET',
        url: '/api/attendances/347',
        response: '@noAttendances'
    }).as('loadAttendances');
};
